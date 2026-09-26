import './bootstrap';
import 'flowbite';

window.initFlowbiteDatepicker = (element) => {
	if (element.flowbiteLivewireBridge) {
		return;
	}

	element.addEventListener('changeDate', () => {
		element.dispatchEvent(new Event('input', { bubbles: true }));
	});

	element.flowbiteLivewireBridge = true;
};
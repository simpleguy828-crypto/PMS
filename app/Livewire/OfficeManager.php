<?php

namespace App\Livewire;

use App\Models\Office;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Concerns\NavigatesPages;


class OfficeManager extends Component
{
    use NavigatesPages;
    use WithPagination;


    public $open = false;
    public $editingOfficeId = null;
    public $name = '';
    public $status = 'active';
    public $computer_count = 0;

    public $search = '';
    public $sortField = 'name';
    public $sortAsc = true;
    public $sortOrder = 'name_asc';
    public $perPage = 10;

    protected $layout = 'components.layouts.app';

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:offices,name,' . $this->editingOfficeId,
            ],
            'status' => 'required|in:active,inactive',
            'computer_count' => 'required|integer|min:0',
        ];
    }

    public function render()
    {
        $offices = Office::when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate($this->perPage);

        return view('livewire.office-manager', [
            'offices' => $offices,
            'sortOptions' => [
                ['label' => 'Name: A to Z', 'value' => 'name_asc'],
                ['label' => 'Name: Z to A', 'value' => 'name_desc'],
                ['label' => 'Computers: low to high', 'value' => 'computers_asc'],
                ['label' => 'Computers: high to low', 'value' => 'computers_desc'],
                ['label' => 'Status: A to Z', 'value' => 'status_asc'],
                ['label' => 'Status: Z to A', 'value' => 'status_desc'],
            ],
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage($value)
    {
        $this->perPage = in_array((int) $value, [10, 25, 50, 100], true) ? (int) $value : 10;
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if (!in_array($field, ['name', 'computer_count', 'status'], true)) {
            return;
        }

        $this->resetPage();

        if ($this->sortField === $field) {
            $this->sortAsc = ! $this->sortAsc;
        } else {
            $this->sortField = $field;
            $this->sortAsc = true;
        }

        $order = $this->sortAsc ? 'asc' : 'desc';
        $this->sortOrder = $this->sortField === 'computer_count'
            ? 'computers_' . $order
            : $this->sortField . '_' . $order;
    }

    public function updatedSortOrder($value)
    {
        $sorts = [
            'name_asc' => ['name', true],
            'name_desc' => ['name', false],
            'computers_asc' => ['computer_count', true],
            'computers_desc' => ['computer_count', false],
            'status_asc' => ['status', true],
            'status_desc' => ['status', false],
        ];
        $this->sortOrder = array_key_exists($value, $sorts) ? $value : 'name_asc';
        [$this->sortField, $this->sortAsc] = $sorts[$this->sortOrder];
        $this->resetPage();
    }

    public function createOffice()
    {
        $this->editingOfficeId = null;
        $this->resetInput();
        $this->resetValidation();
        $this->open = true;
    }

    public function editOffice($id)
    {
        $office = Office::find($id);

        if ($office) {
            $this->editingOfficeId = $id;
            $this->name = $office->name;
            $this->status = $office->status;
            $this->computer_count = $office->computer_count;
        } else {
            $this->editingOfficeId = null;
            $this->resetInput();
        }

        $this->resetValidation();
        $this->open = true;
    }

    public function saveOffice()
    {
        $this->validate();

        if ($this->editingOfficeId) {
            Office::findOrFail($this->editingOfficeId)->update([
                'name' => $this->name,
                'status' => $this->status,
                'computer_count' => $this->computer_count,
            ]);
            session()->flash('message', 'Office updated successfully.');
        } else {
            Office::create([
                'name' => $this->name,
                'status' => $this->status,
                'computer_count' => $this->computer_count,
            ]);
            session()->flash('message', 'Office created successfully.');
        }

        $this->closeModal();
    }

    public function closeModal()
    {
        $this->open = false;
        $this->editingOfficeId = null;
        $this->resetInput();
        $this->resetValidation();
    }

    private function resetInput()
    {
        $this->name = '';
        $this->status = 'active';
        $this->computer_count = 0;
    }

    public function deleteOffice($id)
    {
        Office::findOrFail($id)->delete();
        session()->flash('message', 'Office deleted successfully.');

        if ($this->getPage() > 1 && Office::count() <= ($this->getPage() - 1) * $this->perPage) {
            $this->previousPage();
        }
    }
}
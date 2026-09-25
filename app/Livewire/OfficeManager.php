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
            ->paginate(10);

        return view('livewire.office-manager', compact('offices'));
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortAsc = ! $this->sortAsc;
        } else {
            $this->sortField = $field;
            $this->sortAsc = true;
        }
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

        if ($this->getPage() > 1 && Office::count() <= ($this->getPage() - 1) * 10) {
            $this->previousPage();
        }
    }
}
<?php

namespace App\Livewire;

use App\Models\PmRecord;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Table;
use PhpOffice\PhpWord\Style\Cell;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Style\Border;
use App\Livewire\Concerns\NavigatesPages;

class PmRecordsList extends Component
{
    use NavigatesPages;
    use WithPagination;

    public $search = '';
    public $sortField = 'date_started';
    public $sortAsc = false;
    public $perPage = 15;

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->search = $this->sanitizeString($this->search);
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        // Ensure perPage is a valid integer
        $this->perPage = intval($this->perPage);
        if ($this->perPage <= 0) {
            $this->perPage = 15;
        }
    }

    public function sortBy($field)
    {
        // Sanitize the field name
        $field = $this->sanitizeString($field);

        // Validate the field against a list of allowed columns to prevent SQL injection
        $allowedFields = ['date_started', 'requested_by_name', 'position', 'office.name'];
        if (!in_array($field, $allowedFields)) {
            $field = 'date_started';
        }

        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
            $this->sortField = $field;
        }
    }

    public function getRecordsProperty()
    {
        return PmRecord::with(['office'])
            ->when($this->search, function ($query) {
                $query->whereHas('office', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })->orWhere('requested_by_name', 'like', '%' . $this->search . '%')
                  ->orWhere('position', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate($this->perPage);
    }

    /**
     * Define custom styles that match the PDF template
     */
    private function defineStyles($phpWord)
    {
        // Font styles
        $fontStyle = new Font();
        $fontStyle->setName('Helvetica');
        $fontStyle->setSize(11);
        $phpWord->addFontStyle('Normal', $fontStyle);

        $boldFontStyle = clone $fontStyle;
        $boldFontStyle->setBold(true);
        $phpWord->addFontStyle('Bold', $boldFontStyle);

        $smallFontStyle = clone $fontStyle;
        $smallFontStyle->setSize(9);
        $phpWord->addFontStyle('Small', $smallFontStyle);

        $tinyFontStyle = clone $fontStyle;
        $tinyFontStyle->setSize(7);
        $phpWord->addFontStyle('Tiny', $tinyFontStyle);

        // Paragraph styles
        $normalParagraph = new Paragraph();
        $normalParagraph->setSpaceAfter(200); // 200 twip = 10pt after
        $phpWord->addParagraphStyle('Normal', $normalParagraph);

        $centeredParagraph = clone $normalParagraph;
        $centeredParagraph->setAlignment('center');
        $phpWord->addParagraphStyle('Centered', $centeredParagraph);

        $rightParagraph = clone $normalParagraph;
        $rightParagraph->setAlignment('right');
        $phpWord->addParagraphStyle('Right', $rightParagraph);
    }

    /**
     * Sanitize a string to ensure it is valid UTF-8.
     *
     * @param  mixed  $value
     * @return string
     */
    protected function sanitizeString($value)
    {
        if (is_null($value)) {
            return '';
        }

        // Ensure we are working with a string
        $string = (string) $value;

        // If the string is already valid UTF-8, return it as is.
        if (mb_check_encoding($string, 'UTF-8')) {
            return $string;
        }

        // Attempt to convert to UTF-8, replacing invalid characters.
        return mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    }

    public function render()
    {
        return view('livewire.pm-records-list', [
            'records' => $this->records,
        ]);
    }
}
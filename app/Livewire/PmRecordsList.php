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
    public $sortOrder = 'desc';
    public $sortField = 'date_started';
    public $sortAsc = false;
    public $perPage = 10;

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->search = $this->sanitizeString($this->search);
        $this->resetPage();
    }

    public function updatedPerPage($value)
    {
        $this->perPage = in_array((int) $value, [10, 25, 50, 100], true) ? (int) $value : 10;
        $this->resetPage();
    }

    public function updatedSortOrder($value)
    {
        $sorts = [
            'date_desc' => ['date_started', false],
            'date_asc' => ['date_started', true],
            'name_asc' => ['requested_by_name', true],
            'name_desc' => ['requested_by_name', false],
            'office_asc' => ['office', true],
            'office_desc' => ['office', false],
            'position_asc' => ['position', true],
            'position_desc' => ['position', false],
        ];
        $this->sortOrder = array_key_exists($value, $sorts) ? $value : 'date_desc';
        [$this->sortField, $this->sortAsc] = $sorts[$this->sortOrder];

        $this->resetPage();
    }

    public function sortBy($field)
    {
        if (!in_array($field, ['date_started', 'requested_by_name', 'office', 'position'], true)) {
            return;
        }

        $this->sortAsc = $this->sortField === $field ? !$this->sortAsc : true;
        $this->sortField = $field;
        $direction = $this->sortAsc ? 'asc' : 'desc';
        $this->sortOrder = match ($field) {
            'date_started' => 'date_' . $direction,
            'requested_by_name' => 'name_' . $direction,
            'office' => 'office_' . $direction,
            'position' => 'position_' . $direction,
        };
        $this->resetPage();
    }

    public function getRecordsProperty()
    {
        $query = PmRecord::with(['office', 'recordItems.checklistItem', 'conductedBy'])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->whereHas('office', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    })->orWhere('requested_by_name', 'like', '%' . $this->search . '%')
                      ->orWhere('position', 'like', '%' . $this->search . '%')
                      ->orWhereHas('conductedBy', function ($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            });

        if ($this->sortField === 'office') {
            $query->join('offices', 'pm_records.office_id', '=', 'offices.id')
                ->select('pm_records.*')
                ->orderBy('offices.name', $this->sortAsc ? 'asc' : 'desc');
        } else {
            $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');
        }

        return $query->orderBy('created_at', $this->sortAsc ? 'asc' : 'desc')
            ->orderBy('id', $this->sortAsc ? 'asc' : 'desc')
            ->paginate($this->perPage);
    }

    public function specsForRecord(PmRecord $record)
    {
        return $record->recordItems
            ->filter(function ($recordItem) {
                $task = strtolower($recordItem->checklistItem->task_name ?? '');

                return filled($recordItem->remarks)
                    && (str_contains($task, 'ram') || str_contains($task, 'storage'));
            })
            ->map(function ($recordItem) {
                $task = strtolower($recordItem->checklistItem->task_name ?? '');
                $specs = trim((string) (preg_split('/\s+[–—-]\s+/u', trim($recordItem->remarks), 2)[0] ?? ''));

                if (preg_match('/^(change|repair|replace|upgrade|increase|clean|service|install|update|reinstall)\b/iu', $specs)) {
                    return null;
                }

                $label = str_contains($task, 'ram') ? 'RAM' : 'Storage';

                return $label . ': ' . $specs;
            })
            ->filter()
            ->unique()
            ->values();
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
            'sortOptions' => [
                ['label' => 'Date/time: newest first', 'value' => 'date_desc'],
                ['label' => 'Date/time: oldest first', 'value' => 'date_asc'],
                ['label' => 'Name: A to Z', 'value' => 'name_asc'],
                ['label' => 'Name: Z to A', 'value' => 'name_desc'],
                ['label' => 'Office: A to Z', 'value' => 'office_asc'],
                ['label' => 'Office: Z to A', 'value' => 'office_desc'],
                ['label' => 'Position: A to Z', 'value' => 'position_asc'],
                ['label' => 'Position: Z to A', 'value' => 'position_desc'],
            ],
        ]);
    }
}
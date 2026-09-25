<?php

namespace App\Livewire;

use App\Models\PmRecord;
use App\Models\Office;
use Carbon\Carbon;
use Livewire\Component;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PmMonthlySummaryReport extends Component
{
    public $month;
    public $year;

    public function mount()
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function generateReport()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfDay();
        $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        $records = PmRecord::whereBetween('date_started', [$startDate, $endDate])
            ->with(['office'])
            ->get();

        $summary = Office::all()->map(function ($office) use ($records) {
            $officeRecords = $records->where('office_id', $office->id);
            $total = $officeRecords->count();
            $pending = $officeRecords->where('status', 'pending')->count();
            $inProgress = $officeRecords->where('status', 'in_progress')->count();
            $completed = $officeRecords->where('status', 'completed')->count();

            return [
                'office' => $office->name,
                'total' => $total,
                'pending' => $pending,
                'in_progress' => $inProgress,
                'completed' => $completed,
            ];
        })->filter(function ($item) {
            return $item['total'] > 0;
        })->sortByDesc('total');

        // Create DOCX
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Title
        $section->addTitle('Monthly Preventive Maintenance Summary Report', 1);
        $section->addText('Month: ' . date('F', mktime(0, 0, 0, $this->month, 1)) . ' ' . $this->year);
        $section->addText('Generated on: ' . now()->toDateTimeString());
        $section->addText('');

        // Summary Table
        $section->addTitle('Summary by Office', 2);
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        $table->addRow();
        $table->addCell(2500)->addText('Office', ['bold' => true]);
        $table->addCell(1500)->addText('Total', ['bold' => true]);
        $table->addCell(1500)->addText('Pending', ['bold' => true]);
        $table->addCell(1500)->addText('In Progress', ['bold' => true]);
        $table->addCell(1500)->addText('Completed', ['bold' => true]);

        foreach ($summary as $item) {
            $table->addRow();
            $table->addCell(2500)->addText($item['office']);
            $table->addCell(1500)->addText($item['total']);
            $table->addCell(1500)->addText($item['pending']);
            $table->addCell(1500)->addText($item['in_progress']);
            $table->addCell(1500)->addText($item['completed']);
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $filename = 'pm-monthly-summary-' . $this->year . '-' . str_pad($this->month, 2, '0', STR_PAD_LEFT) . '.docx';

        // Save to temporary location
        $tmpFile = tempnam(sys_get_temp_dir(), 'pm_monthly_');
        $objWriter->save($tmpFile);

        // Return as download
        return response()->download($tmpFile, $filename)->deleteFileAfterSend(true);
    }

    public function render()
    {
        $months = range(1, 12);
        $years = range(now()->year - 5, now()->year + 5);

        return view('livewire.pm-monthly-summary-report', compact('months', 'years'));
    }

    // Navigation methods
    public function navigateToDashboard()
    {
        return redirect()->route('dashboard');
    }
    public function navigateToOfficeSelection()
    {
        return redirect()->route('office-selection');
    }

    public function navigateToRecords()
    {
        return redirect()->route('pm-records-list');
    }

    public function navigateToOfficeManager()
    {
        return redirect()->route('office-manager');
    }

    public function navigateToScheduleManager()
    {
        // For now, redirect to schedule manager
        return redirect()->route('pm-schedule-manager');
    }
}

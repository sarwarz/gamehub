<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportExportReady extends Notification
{
    use Queueable;

    public function __construct(
        private string $filename,
        private string $reportType,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $label = str_replace('_', ' ', ucfirst($this->reportType));

        return [
            'title'        => "{$label} Report Ready",
            'message'      => "Your {$label} report export is ready for download.",
            'filename'     => $this->filename,
            'download_url' => route('admin.reports.export.download', ['filename' => $this->filename]),
            'type'         => 'report_export',
            'icon'         => 'tabler-file-download',
        ];
    }
}

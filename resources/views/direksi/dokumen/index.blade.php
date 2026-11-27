@extends('layouts.simship')

@section('title', 'Dokumen')
@section('crumb', 'Dokumen')

@section('content')
    @php $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.'); @endphp

    <h1>Dokumen</h1>
    <p class="subtitle">Invoice dan technical report dibuat setelah tagihan disetujui.</p>

    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:14px 20px; border-bottom:1px solid #e2e8f0;">
            <h3 style="margin:0;">Invoice</h3>
            <p class="muted" style="margin:4px 0 0;">Draf dapat direvisi. Ekspor pertama menerbitkan invoice.</p>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>NOMOR INVOICE</th>
                        <th>NAMA KAPAL</th>
                        <th>TANGGAL</th>
                        <th>TOTAL</th>
                        <th>STATUS</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="nowrap"><strong style="color:#1d4ed8;">{{ $invoice->invoice_number }}</strong></td>
                            <td>{{ $invoice->ship_name }}</td>
                            <td class="nowrap">{{ $invoice->issue_date?->format('d M Y') ?? '-' }}</td>
                            <td class="nowrap">{{ $rupiah($invoice->total_amount) }}</td>
                            <td>
                                <span class="badge badge-{{ $invoice->status }}">
                                    {{ $invoice->status === App\Models\Invoice::STATUS_TERBIT ? 'Terbit' : 'Draft' }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    @if ($invoice->status === App\Models\Invoice::STATUS_DRAFT)
                                        <a href="{{ route('direksi.dokumen.edit-invoice', $invoice) }}" class="btn btn-light" style="padding:6px 10px;">Ubah</a>
                                    @endif
                                    <a href="{{ route('direksi.dokumen.download-invoice', $invoice) }}" class="btn btn-light" style="padding:6px 10px;">Unduh PDF</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">Belum ada invoice.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 20px; border-top:1px solid #e2e8f0;">
            <span class="muted" style="font-size:12px;">Menampilkan {{ $invoices->total() }} invoice</span>
            <div class="pagination">{{ $invoices->onEachSide(1)->links() }}</div>
        </div>
    </div>

    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:14px 20px; border-bottom:1px solid #e2e8f0;">
            <h3 style="margin:0;">Technical Report</h3>
            <p class="muted" style="margin:4px 0 0;">Laporan teknis tanpa nilai harga.</p>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>NOMOR LAPORAN</th>
                        <th>NAMA KAPAL</th>
                        <th>NOMOR LAMBUNG</th>
                        <th>TANGGAL</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        <tr>
                            <td class="nowrap"><strong style="color:#1d4ed8;">{{ $report->report_number }}</strong></td>
                            <td>{{ $report->ship_name }}</td>
                            <td>{{ $report->hull_number }}</td>
                            <td class="nowrap">{{ $report->issue_date?->format('d M Y') ?? '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('direksi.dokumen.unduh') }}">
                                    @csrf
                                    <input type="hidden" name="jenis" value="technical_report">
                                    <input type="hidden" name="id" value="{{ $report->id }}">
                                    <button type="submit" class="btn btn-light" style="padding:6px 10px;">Unduh PDF</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">Belum ada technical report.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 20px; border-top:1px solid #e2e8f0;">
            <span class="muted" style="font-size:12px;">Menampilkan {{ $reports->total() }} laporan</span>
            <div class="pagination">{{ $reports->onEachSide(1)->links() }}</div>
        </div>
    </div>
@endsection

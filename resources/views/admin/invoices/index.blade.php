@extends('layouts.simship')

@section('title', 'Invoice')
@section('crumb', 'Invoice')

@section('content')
    @php $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.'); @endphp

    <h1>Invoice</h1>
    <p class="subtitle">Draf invoice dapat direvisi sebelum terbit. Invoice terbit dapat diekspor ke PDF.</p>

    <div class="card" style="padding:0; overflow:hidden;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>NOMOR INVOICE</th>
                        <th>NOMOR TAGIHAN</th>
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
                            <td class="nowrap">{{ $invoice->billing->billing_number ?? '-' }}</td>
                            <td>{{ $invoice->ship_name }}</td>
                            <td class="nowrap">{{ $invoice->issue_date?->format('d M Y') ?? '-' }}</td>
                            <td class="nowrap">{{ $rupiah($invoice->total_amount) }}</td>
                            <td><span class="badge badge-{{ $invoice->status }}">{{ $invoice->status === App\Models\Invoice::STATUS_DRAFT ? 'Draft' : 'Terbit' }}</span></td>
                            <td>
                                <div class="actions">
                                    @if ($invoice->status === App\Models\Invoice::STATUS_DRAFT)
                                        <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-light" style="padding:6px 10px;">Ubah</a>
                                    @endif
                                    <a href="{{ route('admin.invoices.download', $invoice) }}" class="btn btn-light" style="padding:6px 10px;">Unduh PDF</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">Belum ada invoice. Invoice dibuat setelah Direksi menyetujui tagihan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 20px; border-top:1px solid #e2e8f0;">
            <span class="muted" style="font-size:12px;">Menampilkan {{ $invoices->total() }} data</span>
            <div class="pagination">{{ $invoices->onEachSide(1)->links() }}</div>
        </div>
    </div>
@endsection

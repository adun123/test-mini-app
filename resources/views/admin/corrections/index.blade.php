@extends('layouts.app', ['title' => 'Correction Requests'])

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Correction Requests</h1>
        <p class="page-subtitle">Review pengajuan koreksi attendance dari employee.</p>
    </div>
</div>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Requested Change</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Admin Note</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($corrections as $correction)
                    <tr>
                        <td>
                            <strong>{{ $correction->employee->user->name ?? '-' }}</strong>
                            <div class="muted">{{ $correction->employee->employee_code ?? '-' }}</div>
                        </td>
                        <td>{{ optional($correction->attendance->attendance_date)->format('Y-m-d') ?? '-' }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($correction->type)) }}</td>
                        <td>
                            @if($correction->requested_check_in_time)
                                <div>Check In: {{ $correction->requested_check_in_time->format('H:i') }}</div>
                            @endif
                            @if($correction->requested_check_out_time)
                                <div>Check Out: {{ $correction->requested_check_out_time->format('H:i') }}</div>
                            @endif
                            @if($correction->requested_location)
                                <div>Location: {{ $correction->requested_location }}</div>
                            @endif
                            @if($correction->requested_latitude && $correction->requested_longitude)
                                <a class="button light" style="margin-top: 6px;" href="https://www.google.com/maps?q={{ $correction->requested_latitude }},{{ $correction->requested_longitude }}" target="_blank">Open Maps</a>
                            @endif
                            @if($correction->requested_note)
                                <div>Note: {{ $correction->requested_note }}</div>
                            @endif
                            @if(!$correction->requested_check_in_time && !$correction->requested_check_out_time && !$correction->requested_location && !$correction->requested_note)
                                -
                            @endif
                        </td>
                        <td>{{ $correction->reason }}</td>
                        <td>
                            <span class="badge {{ $correction->status === 'approved' ? 'success' : ($correction->status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($correction->status) }}
                            </span>
                        </td>
                        <td>{{ $correction->admin_note ?: '-' }}</td>
                        <td>
                            @if($correction->status === 'pending')
                                <form method="POST" action="{{ route('admin.corrections.update', $correction) }}" style="min-width: 240px;">
                                    @csrf
                                    @method('PATCH')
                                    <div class="field">
                                        <textarea name="admin_note" rows="2" placeholder="Catatan HR"></textarea>
                                    </div>
                                    <div class="actions">
                                        <button class="button" name="status" value="approved" type="submit">Approve</button>
                                        <button class="button danger" name="status" value="rejected" type="submit">Reject</button>
                                    </div>
                                </form>
                            @else
                                <span class="muted">Reviewed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">Belum ada correction request.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: 16px;">{{ $corrections->links() }}</div>
</section>
@endsection

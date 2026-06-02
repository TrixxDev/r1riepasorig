@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4">
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="mb-0"><i class="fa-solid fa-file-lines"></i> Pasūtījumu žurnāls</h3>
      <a href="{{ route('admin.orders') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Atpakaļ uz pasūtījumiem
      </a>
    </div>
    <div class="card-body" style="max-height: 75vh; overflow-y: auto;">
      @if (!empty($groupedLogs))
        <div class="logs-wrapper">
          @foreach ($groupedLogs as $group)
            <div class="order-group">
              <div class="order-group-header">
                <h5 class="order-group-title">
                  <i class="fa-solid fa-receipt"></i>
                  @if ($group['order_number'])
                    Pasūtījums #{{ $group['order_number'] }}
                  @elseif ($group['order_id'])
                    Pasūtījums ID: {{ $group['order_id'] }}
                  @else
                    Sistēmas ieraksti
                  @endif
                </h5>
                <div class="order-group-meta">
                  @if ($group['customer_email'])
                    <span><i class="fa-solid fa-envelope"></i> {{ $group['customer_email'] }}</span>
                  @endif
                  @if ($group['order_id'])
                    <span><i class="fa-solid fa-id-card"></i> ID: {{ $group['order_id'] }}</span>
                  @endif
                </div>
              </div>

              <div class="timeline">
                @foreach ($group['entries'] as $entry)
                  <div class="timeline-entry timeline-entry-{{ $entry['severity'] }}">
                    <div class="timeline-icon">
                      @if ($entry['severity'] === 'error')
                        <i class="fa-solid fa-triangle-exclamation"></i>
                      @elseif ($entry['severity'] === 'success')
                        <i class="fa-solid fa-circle-check"></i>
                      @else
                        <i class="fa-solid fa-circle-info"></i>
                      @endif
                    </div>
                    <div class="timeline-content">
                      <div class="timeline-header">
                        <span class="timeline-title">{{ $entry['message'] }}</span>
                        @if ($entry['timestamp'])
                          <span class="timeline-time"><i class="fa-solid fa-clock"></i> {{ $entry['timestamp'] }}</span>
                        @endif
                      </div>
                      @if (!empty($entry['context']))
                        <div class="timeline-context">
                          @foreach ($entry['context'] as $key => $value)
                            <div class="context-item"><strong>{{ $key }}:</strong> {{ is_scalar($value) ? $value : json_encode($value) }}</div>
                          @endforeach
                        </div>
                      @endif
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="alert alert-info mb-0">
          <i class="fa-solid fa-circle-info"></i> Žurnāls pašlaik ir tukšs.
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
.logs-wrapper {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.order-group {
  border: 1px solid #e9ecef;
  border-radius: 10px;
  padding: 18px;
  margin-bottom: 20px;
  background: #ffffff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.order-group-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 16px;
}

.order-group-title {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #212529;
  display: flex;
  align-items: center;
  gap: 8px;
}

.order-group-meta {
  display: flex;
  gap: 12px;
  color: #6c757d;
  font-size: 13px;
}

.order-group-meta span {
  display: flex;
  align-items: center;
  gap: 6px;
}

.timeline {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.timeline-entry {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.timeline-entry-error {
  border-left: 4px solid #dc3545;
}

.timeline-entry-success {
  border-left: 4px solid #28a745;
}

.timeline-entry-info {
  border-left: 4px solid #17a2b8;
}

.timeline-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  color: inherit;
}

.timeline-entry-error .timeline-icon {
  color: #dc3545;
}

.timeline-entry-success .timeline-icon {
  color: #28a745;
}

.timeline-entry-info .timeline-icon {
  color: #17a2b8;
}

.timeline-content {
  flex: 1;
}

.timeline-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 8px;
}

.timeline-title {
  font-weight: 600;
  color: #212529;
}

.timeline-time {
  font-size: 12px;
  color: #6c757d;
  white-space: nowrap;
}

.timeline-context {
  display: grid;
  gap: 4px;
  font-size: 13px;
  color: #495057;
  background: white;
  padding: 8px;
  border-radius: 6px;
  border: 1px solid #e9ecef;
}

.context-item strong {
  color: #212529;
}
</style>
@endpush

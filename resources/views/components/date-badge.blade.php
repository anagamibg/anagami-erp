{{-- Self-contained date badge — robust against parent .card-header padding overrides --}}
<div class="date-badge">
    <span class="date-badge__month">{{ $month }}</span>
    <span class="date-badge__day">{{ $date }}</span>
</div>

@once
    @push('styles')
        <style>
            .date-badge {
                display: inline-flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 6px 10px;
                min-width: 56px;
                line-height: 1.2;
                border: 1px solid #e9ecef;
                border-radius: 6px;
                background: #fff;
                white-space: nowrap;
            }
            .date-badge__month {
                font-size: 11px;
                text-transform: uppercase;
                color: #6c757d;
                letter-spacing: 0.04em;
            }
            .date-badge__day {
                font-size: 15px;
                font-weight: 600;
                color: #212529;
            }
        </style>
    @endpush
@endonce

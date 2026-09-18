@extends('email.common')

@section('content')
    <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; line-height: 1.6; max-width: 580px; margin: 0 auto;">
        
        <h2 style="color: #1e293b; font-size: 20px; font-weight: 700; margin-top: 0; margin-bottom: 16px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
            {{ __('Full & Final Settlement & Clearance') }}
        </h2>

        {{-- Custom Message Content --}}
        <div style="white-space: pre-wrap; font-size: 14px; margin-bottom: 24px; color: #334155; line-height: 1.7; background: #ffffff; padding: 4px 0;">{{ $custom_message }}</div>

        {{-- Settlement Metadata Box --}}
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px 20px; margin-bottom: 28px;">
            <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                <tr>
                    <td style="padding: 6px 0; color: #64748b; width: 42%;"><strong>{{ __('Settlement Reference:') }}</strong></td>
                    <td style="padding: 6px 0; color: #0f172a; font-weight: 600;">{{ $settlement->settlement_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b;"><strong>{{ __('Employee Name:') }}</strong></td>
                    <td style="padding: 6px 0; color: #0f172a;">{{ $settlement->employee_name }} ({{ $settlement->employee_code }})</td>
                </tr>
                @if(!empty($settlement->department))
                <tr>
                    <td style="padding: 6px 0; color: #64748b;"><strong>{{ __('Department / Designation:') }}</strong></td>
                    <td style="padding: 6px 0; color: #0f172a;">{{ $settlement->department }} / {{ $settlement->designation }}</td>
                </tr>
                @endif
                @if($settlement->net_amount > 0)
                <tr>
                    <td style="padding: 8px 0 4px 0; color: #64748b; border-top: 1px dashed #cbd5e1;"><strong>{{ __('Net Final Payable:') }}</strong></td>
                    <td style="padding: 8px 0 4px 0; color: #16a34a; font-weight: 700; font-size: 16px; border-top: 1px dashed #cbd5e1;">
                        {{ \Auth::user()->priceFormat($settlement->net_amount) }}
                    </td>
                </tr>
                @endif
            </table>
        </div>

        {{-- Primary Action Button --}}
        <div style="text-align: center; margin: 32px 0;">
            <a href="{{ $publicUrl }}" style="background-color: #4f46e5; color: #ffffff; text-decoration: none; padding: 13px 32px; border-radius: 6px; font-weight: 600; font-size: 15px; display: inline-block; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);">
                {{ __('Open & Complete Clearance Form') }} &rarr;
            </a>
        </div>

        {{-- Direct URL Fallback --}}
        <div style="background-color: #f1f5f9; border-radius: 6px; padding: 12px 16px; margin-top: 24px; font-size: 12px; color: #64748b;">
            <p style="margin: 0 0 6px 0; font-weight: 600; color: #475569;">{{ __('Direct Access Link:') }}</p>
            <p style="margin: 0; word-break: break-all;">
                <a href="{{ $publicUrl }}" style="color: #4f46e5; text-decoration: underline;">{{ $publicUrl }}</a>
            </p>
            <p style="margin: 8px 0 0 0; font-size: 11px; color: #94a3b8;">
                {{ __('Note: This link is encrypted and remains active for 30 days.') }}
            </p>
        </div>

        <p style="font-size: 12px; color: #94a3b8; margin-top: 24px; text-align: center;">
            {{ __('This is an automated operational notification regarding your employment exit & settlement.') }}
        </p>
    </div>
@endsection

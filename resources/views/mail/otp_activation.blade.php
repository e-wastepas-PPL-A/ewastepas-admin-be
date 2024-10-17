@extends('mail.template')

@section('content')
    <tr>
        <td style="padding: 48px 24px 0; color: #161c2d; font-size: 18px; font-weight: 600;">
            Hello, {{ $name }}
        </td>
    </tr>
    <tr>
        @if ($type == 'activation')
            <td style="padding: 15px 24px 15px; color: #353434;">
                Untuk melakukan aktivasi akun silahkan konfirmasi OTP anda sebelum <br> <b>{{ $expired }}</b>
            </td>
        @else
            <td style="padding: 15px 24px 15px; color: #353434;">
                Untuk memperbaharui password silahkan konfirmasi OTP anda sebelum <br> <b>{{ $expired }}</b>
            </td>
        @endif

    </tr>

    <tr>
        <td style="padding: 18px 24px;text-align: center;">
            <input
                style="width: 25%; text-align: center;padding: 12px 24px; outline: none; text-decoration: none; font-size: 18px; letter-spacing: 0.7px; transition: all 0.3s; font-weight: 600; border-radius: 6px; background-color: #ffffff; border: 1px solid #2f55d4; color: #2f55d4"
                readonly value="{{ $code }}">
        </td>
    </tr>

    <tr>
        <td style="padding: 15px 10px 0; color: #353434;margin:bottom:10px">
            Jika Anda tidak melakukannya, silahkan abaikan email ini atau
            hubungi customer service kami.
        </td>
    </tr>
@endsection

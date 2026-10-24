<?php

namespace App\Http\Controllers;

use App\Models\Cert\CertStudent;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeController extends Controller
{
    public function verify($hash)
    {
        $certificate = CertStudent::where('verify_hash', $hash)->firstOrFail();

        return view('cert.verify', compact('certificate'));
    }

    public function index(Request $request)
    {
        return QrCode::size(300)
            ->generate(' Salma omar Ali Abdallah');
    }
}

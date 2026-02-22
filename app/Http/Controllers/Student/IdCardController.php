<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\StudentIdCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class IdCardController extends Controller
{
    public function __construct(
        private readonly StudentIdCardService $idCardService
    ) {
    }

    public function show(Request $request): View
    {
        $student = $request->user();
        $booking = $this->idCardService->resolveActiveBooking($student);
        $svg = $booking ? $this->idCardService->buildSvg($student, $booking) : null;

        return view('student.id-card.show', [
            'booking' => $booking,
            'svg' => $svg,
        ]);
    }

    public function downloadSvg(Request $request): Response|RedirectResponse
    {
        $student = $request->user();
        $booking = $this->idCardService->resolveActiveBooking($student);
        if (!$booking) {
            return back()->with('error', 'ID card is available only while your booking is active.');
        }

        $svg = $this->idCardService->buildSvg($student, $booking);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="' . $this->idCardService->buildDownloadFileName($student, 'svg') . '"',
        ]);
    }

    public function downloadPng(Request $request): Response|RedirectResponse
    {
        $student = $request->user();
        $booking = $this->idCardService->resolveActiveBooking($student);
        if (!$booking) {
            return back()->with('error', 'ID card is available only while your booking is active.');
        }

        try {
            $svg = $this->idCardService->buildSvg($student, $booking);
            $binary = $this->idCardService->generatePngBinary($svg, 2);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return response($binary, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="' . $this->idCardService->buildDownloadFileName($student, 'png') . '"',
        ]);
    }

    public function downloadPdf(Request $request): Response|RedirectResponse
    {
        $student = $request->user();
        $booking = $this->idCardService->resolveActiveBooking($student);
        if (!$booking) {
            return back()->with('error', 'ID card is available only while your booking is active.');
        }

        $svg = $this->idCardService->buildSvg($student, $booking);
        try {
            $imageDataUri = $this->idCardService->buildPngDataUri($svg, 2);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $pdf = \PDF::loadView('student.id-card.pdf', [
            'imageDataUri' => $imageDataUri,
            'student' => $student,
            'booking' => $booking,
        ]);

        return $pdf->download($this->idCardService->buildDownloadFileName($student, 'pdf'));
    }
}

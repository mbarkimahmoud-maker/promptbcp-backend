<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PromptExecution;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFController extends Controller
{
    public function generatePDF(Request $request, PromptExecution $execution)
    {
        $request->validate([
            'html_content' => 'required|string',
        ]);

        $pdf = Pdf::loadView('pdf.ai-response', [
            'htmlContent' => $request->html_content,
            'promptTitle' => $execution->prompt->title,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('reponse_' . time() . '.pdf');
    }
}
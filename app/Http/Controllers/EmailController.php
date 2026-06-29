<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\AIResponseMail;
use App\Models\PromptExecution;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function sendAIResponse(Request $request, PromptExecution $execution)
{
    $request->validate([
        'emails'       => 'required|array|min:1',
        'emails.*'     => 'email',
        'html_content' => 'required|string',
    ]);

    $errors = [];
    $sent = 0;

    foreach ($request->emails as $email) {
        try {
            Mail::to($email)->send(
                new AIResponseMail($request->html_content, $execution->prompt->title)
            );
            $sent++;
        } catch (\Exception $e) {
            $errors[] = $email;
        }
    }

    if (count($errors) > 0) {
        return response()->json([
            'message' => "Envoyé à $sent destinataire(s), échec pour : " . implode(', ', $errors),
        ], 207); // 207 = succès partiel
    }

    return response()->json([
        'message' => "Email envoyé à $sent destinataire(s) avec succès",
    ]);
}
}
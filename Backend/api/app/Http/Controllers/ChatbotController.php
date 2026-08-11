<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    // Lista de preguntas frecuentes agrupadas por categoría, para mostrar como accesos rápidos en el chat.
    public function preguntasFrecuentes()
    {
        return Faq::where('faqActivo', true)
            ->orderBy('faqCategoria')
            ->get(['faqId', 'faqCategoria', 'faqPregunta'])
            ->groupBy('faqCategoria');
    }

    // Recibe un mensaje libre del paciente y devuelve la FAQ que mejor coincide,
    // por conteo de palabras clave en común. Sin llamadas a APIs externas.
    public function consultar(Request $request)
    {
        $request->validate(['mensaje' => 'required|string|max:300']);

        $mensaje = $this->normalizar($request->mensaje);
        $palabrasMensaje = array_filter(explode(' ', $mensaje), fn ($p) => strlen($p) > 2);

        $faqs = Faq::where('faqActivo', true)->get();

        $mejorFaq = null;
        $mejorPuntaje = 0;

        foreach ($faqs as $faq) {
            $textoFaq = $this->normalizar($faq->faqPalabrasClave . ' ' . $faq->faqPregunta);
            $puntaje = 0;
            foreach ($palabrasMensaje as $palabra) {
                if (str_contains($textoFaq, $palabra)) {
                    $puntaje++;
                }
            }
            if ($puntaje > $mejorPuntaje) {
                $mejorPuntaje = $puntaje;
                $mejorFaq = $faq;
            }
        }

        if (!$mejorFaq) {
            return response()->json([
                'encontrado' => false,
                'respuesta' => 'No tengo una respuesta para eso todavía. Puedes revisar las preguntas frecuentes de abajo o contactar directamente a recepción.',
            ]);
        }

        return response()->json([
            'encontrado' => true,
            'faqId' => $mejorFaq->faqId,
            'pregunta' => $mejorFaq->faqPregunta,
            'respuesta' => $mejorFaq->faqRespuesta,
        ]);
    }

    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));
        $mapa = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n'];
        return strtr($texto, $mapa);
    }
}

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
    // comparando SOLO contra las palabras clave curadas de cada FAQ (no contra el
    // texto de la pregunta, que tiene conectores como "que"/"para" que generaban
    // falsos positivos). Sin llamadas a APIs externas.
    public function consultar(Request $request)
    {
        $request->validate(['mensaje' => 'required|string|max:300']);

        $mensaje = $this->normalizar($request->mensaje);

        $faqs = Faq::where('faqActivo', true)->get();

        $mejorFaq = null;
        $mejorPuntaje = 0;

        foreach ($faqs as $faq) {
            $frasesClave = array_map('trim', explode(',', $this->normalizar($faq->faqPalabrasClave)));
            $puntaje = 0;
            foreach ($frasesClave as $frase) {
                if ($frase !== '' && preg_match('/\b' . preg_quote($frase, '/') . '\b/', $mensaje)) {
                    // Frases de más de una palabra pesan más: son más específicas
                    // y menos propensas a coincidir por accidente.
                    $puntaje += substr_count($frase, ' ') > 0 ? 2 : 1;
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

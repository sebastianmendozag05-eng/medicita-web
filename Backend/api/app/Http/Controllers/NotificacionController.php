<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->notificaciones()->orderByDesc('created_at')->get()
        );
    }

    public function marcarLeida(Request $request, $id)
    {
        $notif = $request->user()->notificaciones()->findOrFail($id);
        $notif->update(['leida' => true]);
        return response()->json($notif);
    }

    public function marcarTodasLeidas(Request $request)
    {
        $request->user()->notificaciones()->update(['leida' => true]);
        return response()->json(['message' => 'Notificaciones marcadas como leídas']);
    }
}

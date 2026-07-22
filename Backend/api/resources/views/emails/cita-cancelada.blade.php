<x-mail::message>
# Tu cita ha sido cancelada

Hola **{{ $cita->paciente->pacNombre }} {{ $cita->paciente->pacApePat }}**,

Tu cita médica ha sido cancelada.

**Detalles:**
- **Médico:** Dr. {{ $cita->medico->medNombre }} {{ $cita->medico->medApePat }}
- **Fecha:** {{ \Carbon\Carbon::parse($cita->citFecha)->format('d/m/Y') }}
- **Hora:** {{ $cita->citHora }}
@if($cita->citMotivoCancela)
- **Motivo de cancelación:** {{ $cita->citMotivoCancela }}
@endif

<x-mail::button :url="'http://localhost:5173'">
Agendar una nueva cita
</x-mail::button>

Gracias por usar **MediCita**.
</x-mail::message>

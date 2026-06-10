<x-mail::message>
# Tu cita ha sido confirmada

Hola **{{ $cita->paciente->pacNombre }} {{ $cita->paciente->pacApePat }}**,

Tu cita médica ha sido agendada exitosamente.

**Detalles:**
- **Médico:** Dr. {{ $cita->medico->medNombre }} {{ $cita->medico->medApePat }}
- **Fecha:** {{ \Carbon\Carbon::parse($cita->citFecha)->format('d/m/Y') }}
- **Hora:** {{ $cita->citHora }}
- **Motivo:** {{ $cita->citMotivo }}

<x-mail::button :url="'http://localhost:5173'">
Ver mis citas
</x-mail::button>

Gracias por usar **MediCita**.
</x-mail::message>
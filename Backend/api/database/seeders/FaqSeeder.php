<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faq;

class FaqSeeder extends Seeder
{
    /**
     * Preguntas frecuentes del chatbot de pacientes.
     * Idempotente: usa updateOrCreate por pregunta, seguro de correr varias veces.
     * php artisan db:seed --class=FaqSeeder
     */
    public function run(): void
    {
        $faqs = [
            [
                'faqCategoria' => 'citas',
                'faqPregunta' => '¿Cómo agendo una cita?',
                'faqRespuesta' => 'Ve a "Mis Citas" en el menú lateral, elige la especialidad, el médico, la fecha y una hora disponible, y confirma. Solo se muestran horarios realmente libres para ese médico.',
                'faqPalabrasClave' => 'agendar,agendo,agenda,nueva cita,reservar,programar,como pido',
            ],
            [
                'faqCategoria' => 'citas',
                'faqPregunta' => '¿Cómo cancelo una cita?',
                'faqRespuesta' => 'En "Mis Citas" busca la cita que quieres cancelar y presiona "Cancelar". Recibirás una notificación confirmando el cambio.',
                'faqPalabrasClave' => 'cancelar,cancelo,cancelacion,anular,quitar cita',
            ],
            [
                'faqCategoria' => 'citas',
                'faqPregunta' => '¿Puedo reagendar una cita a otro horario?',
                'faqRespuesta' => 'Por ahora no hay un botón directo de "reagendar". Debes cancelar la cita actual y agendar una nueva en el horario que prefieras.',
                'faqPalabrasClave' => 'reagendar,cambiar fecha,mover cita,otro horario,reprogramar',
            ],
            [
                'faqCategoria' => 'citas',
                'faqPregunta' => '¿Por qué no aparecen horarios disponibles para un médico?',
                'faqRespuesta' => 'Puede ser que ese día no corresponda al turno laboral del médico, que esté ausente (vacaciones o incapacidad), o que ya no queden horarios libres ese día. Intenta con otra fecha.',
                'faqPalabrasClave' => 'sin horarios,no hay horarios,no disponible,vacio,ausente',
            ],
            [
                'faqCategoria' => 'citas',
                'faqPregunta' => '¿Cómo sé si mi cita fue confirmada?',
                'faqRespuesta' => 'Recibirás una notificación dentro de la plataforma y un correo de confirmación en cuanto se agenda la cita. También puedes revisar el estatus en "Mis Citas".',
                'faqPalabrasClave' => 'confirmada,confirmacion,estatus,estado de mi cita',
            ],
            [
                'faqCategoria' => 'cuenta',
                'faqPregunta' => '¿Cómo recupero mi contraseña?',
                'faqRespuesta' => 'En la pantalla de inicio de sesión, presiona "¿Olvidaste tu contraseña?", ingresa tu correo y sigue el enlace que te llegará por email para crear una nueva.',
                'faqPalabrasClave' => 'contraseña,password,olvide,recuperar,reset',
            ],
            [
                'faqCategoria' => 'cuenta',
                'faqPregunta' => '¿Cómo actualizo mis datos personales?',
                'faqRespuesta' => 'Ve a "Mi Perfil" en el menú lateral. Ahí puedes editar tu teléfono, peso, estatura y otros datos de contacto.',
                'faqPalabrasClave' => 'editar perfil,actualizar datos,cambiar telefono,mis datos',
            ],
            [
                'faqCategoria' => 'cuenta',
                'faqPregunta' => '¿Quién puede ver mi expediente médico?',
                'faqRespuesta' => 'Solo tú y el médico que te atiende pueden ver el detalle de tu expediente e historial. El personal administrativo no tiene acceso a esa información clínica.',
                'faqPalabrasClave' => 'privacidad,quien ve,expediente,confidencial,seguridad datos',
            ],
            [
                'faqCategoria' => 'general',
                'faqPregunta' => '¿Qué especialidades médicas tiene MediCita?',
                'faqRespuesta' => 'Actualmente puedes agendar con Medicina General, Pediatría, Cardiología y Ginecología. Verás las opciones disponibles al agendar tu cita.',
                'faqPalabrasClave' => 'especialidades,que doctores,tipos de medico',
            ],
            [
                'faqCategoria' => 'general',
                'faqPregunta' => '¿Dónde veo mi historial médico?',
                'faqRespuesta' => 'En la sección "Historial Médico" del menú lateral encontrarás tus consultas pasadas, diagnósticos y notas registradas por tus médicos.',
                'faqPalabrasClave' => 'historial,consultas pasadas,diagnosticos anteriores',
            ],
            [
                'faqCategoria' => 'general',
                'faqPregunta' => 'Tengo un síntoma o malestar, ¿qué hago?',
                'faqRespuesta' => 'Este chat no da diagnósticos ni consejos médicos. Si tienes un síntoma o malestar, agenda una cita con el médico correspondiente desde "Mis Citas". Si es una urgencia, acude directamente a un servicio de urgencias en vez de esperar una cita.',
                'faqPalabrasClave' => 'dolor,me duele,sintoma,malestar,me siento mal,fiebre,mareo,urgencia,emergencia',
            ],
            [
                'faqCategoria' => 'general',
                'faqPregunta' => '¿Cómo contacto a la recepción si tengo un problema?',
                'faqRespuesta' => 'Este chat solo responde preguntas frecuentes. Para casos que no puedo resolver, comunícate directamente con recepción por teléfono o acude a la clínica.',
                'faqPalabrasClave' => 'contacto,ayuda humana,hablar con alguien,recepcion,soporte',
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(['faqPregunta' => $faq['faqPregunta']], $faq);
        }
    }
}

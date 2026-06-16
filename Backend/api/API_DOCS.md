# MediCita API — Documentación

Base URL: `http://localhost:8000/api/v1`

## Auth
| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| POST | `/register` | Registro de usuario | No |
| POST | `/login` | Login → token | No |
| POST | `/logout` | Cerrar sesión | Sí |

**Login body:**
```json
{"email":"admin@medicita.com","password":"12345678"}
```

## Citas
| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/citas` | Listar citas | Sí |
| POST | `/citas` | Crear cita + envía correo | Sí |
| GET | `/citas/{id}` | Ver cita | Sí |
| PUT | `/citas/{id}` | Actualizar cita | Sí |
| DELETE | `/citas/{id}` | Cancelar cita | Sí |

## Médicos
| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/medicos` | Listar médicos | Sí |
| POST | `/medicos` | Crear médico | Sí |
| GET | `/medicos/{id}` | Ver médico + turno | Sí |
| PUT | `/medicos/{id}` | Actualizar médico | Sí |
| DELETE | `/medicos/{id}` | Desactivar médico | Sí |

## Pacientes
| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/pacientes` | Listar pacientes | Sí |
| POST | `/pacientes` | Crear paciente | Sí |
| GET | `/pacientes/{id}` | Ver paciente + expediente | Sí |
| PUT | `/pacientes/{id}` | Actualizar paciente | Sí |
| DELETE | `/pacientes/{id}` | Desactivar paciente | Sí |

## Expediente
| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/expediente/{pacId}` | Ver expediente | Sí |
| POST | `/expediente` | Crear expediente | Sí |
| PUT | `/expediente/{pacId}` | Actualizar expediente | Sí |

## Agenda
| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/agenda/medico/{id}?fecha=2026-06-15` | Citas del día | Sí |
| GET | `/agenda/medico/{id}/semana?inicio=2026-06-15&fin=2026-06-21` | Semana | Sí |
| GET | `/agenda/todos?fecha=2026-06-15` | Todos los médicos | Sí |

## Reportes
| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/reportes/resumen` | Resumen general | Sí |
| GET | `/reportes/periodo?inicio=2026-06-01&fin=2026-06-30` | Por período | Sí |
| GET | `/reportes/medico/{id}` | Por médico | Sí |

## Headers requeridos
```
Authorization: Bearer {token}
Content-Type: application/json
```
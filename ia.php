<?php
function generar_contenido(string $tipo,string $materia,string $nivel,string $tema,string $estrategia): array {
 $nombres=['planeacion'=>'planeación didáctica','examen'=>'evaluación formativa','actividad'=>'actividad interactiva'];
 $prompt="Eres asesor pedagógico de EduMentor IA. Crea una {$nombres[$tipo]} para {$nivel}, en {$materia}, sobre {$tema}. Estrategia docente: ".($estrategia?:'aprendizaje activo').". Escribe en español claro. Incluye objetivos, materiales, inicio, desarrollo, cierre, diferenciación, evaluación diagnóstica y final, y registro de evidencias. Si es examen, incluye cinco preguntas con respuestas justificadas. Si es actividad, instrucciones, tiempos y criterios de éxito. No inventes resultados ni datos de alumnos.";
 if (GEMINI_API_KEY !== '') {
    if (!function_exists('curl_init')) throw new RuntimeException('Activa la extensión cURL en PHP.');
    $url='https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode(GEMINI_MODEL).':generateContent';
    $ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>40,CURLOPT_HTTPHEADER=>['Content-Type: application/json','x-goog-api-key: '.GEMINI_API_KEY],CURLOPT_POSTFIELDS=>json_encode(['contents'=>[['parts'=>[['text'=>$prompt]]]]],JSON_UNESCAPED_UNICODE)]);
    $response=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);$err=curl_error($ch);curl_close($ch);
    if ($response===false||$code<200||$code>=300) throw new RuntimeException('Error en Gemini: '.($err?:'HTTP '.$code));
    $data=json_decode($response,true);$text=$data['candidates'][0]['content']['parts'][0]['text']??'';
    if ($text==='') throw new RuntimeException('Gemini no devolvió contenido.');
    return [$text,'IA Gemini'];
 }
 $contenido="DEMOSTRACIÓN LOCAL (sin IA): ".mb_strtoupper($nombres[$tipo])."
Materia: $materia
Nivel: $nivel
Tema: $tema
Estrategia propuesta: ".($estrategia?:'aprendizaje activo')."

OBJETIVO
Al finalizar la sesión, el alumnado explicará y aplicará conceptos básicos del tema «$tema».

INICIO (10 minutos)
Realiza dos preguntas diagnósticas y registra respuestas anónimas.

DESARROLLO (25 minutos)
Organiza equipos pequeños para resolver un problema real relacionado con «$tema».

CIERRE (10 minutos)
Solicita un ejemplo individual y una reflexión de lo aprendido.

EVALUACIÓN
Compara porcentajes antes y después y registra observaciones.

ADAPTACIÓN
Ofrece ejemplos visuales e instrucciones simplificadas.

NOTA
Esta plantilla NO fue generada por IA y debe adaptarse al contexto escolar.";
 if ($tipo==='examen') $contenido.="

PREGUNTAS MODELO
1. Define el concepto principal.
2. Da un ejemplo.
3. Detecta un error frecuente.
4. Resuelve un caso práctico.
5. Explica el procedimiento.
RESPUESTAS: el docente debe elaborar la clave según el currículo.";
 return [$contenido,'demostración local'];
}

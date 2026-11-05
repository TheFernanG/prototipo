<?php
declare(strict_types=1);

function citationChoices(): array {
    if (!role('administrador','docente')) return ['students'=>[],'teachers'=>[]];
    $students=students();
    $ids=array_column($students,'id');
    $parents=[];
    $teachers=[];
    if ($ids) {
        $placeholders=implode(',',array_fill(0,count($ids),'?'));
        foreach (rows("SELECT alumno_id,padre_id FROM vinculos WHERE alumno_id IN ($placeholders)",$ids) as $row) {
            $parents[$row['alumno_id']][]=(int)$row['padre_id'];
        }
        foreach (rows("SELECT DISTINCT i.alumno_id,a.docente_id FROM inscripciones i
            JOIN asignaciones a ON a.grupo_id=i.grupo_id
            WHERE i.alumno_id IN ($placeholders)",$ids) as $row) {
            $teachers[$row['alumno_id']][]=(int)$row['docente_id'];
        }
    }
    foreach ($students as &$student) {
        $student['parents']=$parents[$student['id']]??[];
        $student['teachers']=$teachers[$student['id']]??[];
    }
    unset($student);
    $teacherChoices=role('docente') ? [['id'=>user()['id'],'nombre'=>user()['nombre']]] :
        rows("SELECT id,nombre FROM usuarios WHERE rol='docente' AND activo=1 ORDER BY nombre");
    return ['students'=>$students,'teachers'=>$teacherChoices];
}

function validateCitation(array $recipient): array {
    requireRole('administrador','docente');
    $student=num('cita_alumno_id');
    $teacher=num('cita_docente_id');
    if (!studentAllowed($student)) throw new RuntimeException('Alumno fuera de tus grupos.');
    if ($recipient['rol']==='alumno' && $student!==(int)$recipient['id']) {
        throw new RuntimeException('El citatorio debe corresponder al alumno destinatario.');
    }
    if ($recipient['rol']==='padre' && !one('SELECT id FROM vinculos WHERE padre_id=? AND alumno_id=?',
        [$recipient['id'],$student])) {
        throw new RuntimeException('El alumno no está vinculado al padre destinatario.');
    }
    if (role('docente') && $teacher!==(int)user()['id']) {
        throw new RuntimeException('El citatorio debe llevar tu nombre como docente.');
    }
    $teacherRow=one("SELECT u.nombre FROM usuarios u WHERE u.id=? AND u.rol='docente' AND u.activo=1
        AND EXISTS (SELECT 1 FROM asignaciones a JOIN inscripciones i ON i.grupo_id=a.grupo_id
            WHERE a.docente_id=u.id AND i.alumno_id=?)",[$teacher,$student]);
    if (!$teacherRow) throw new RuntimeException('Selecciona un docente asignado al grupo del alumno.');
    $studentRow=one("SELECT nombre FROM usuarios WHERE id=? AND rol='alumno' AND activo=1",[$student]);
    if (!$studentRow) throw new RuntimeException('Alumno no disponible.');
    $date=dateValue('fecha_cita');
    $time=val('hora_cita',5);
    if (!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D',$time)) {
        throw new RuntimeException('Escribe una hora válida para la cita.');
    }
    return [$studentRow['nombre'],$teacherRow['nombre'],$date,$time.':00'];
}

function citationForMessage(array $message): array {
    $citation=one('SELECT alumno_nombre,docente_nombre,fecha_cita,hora_cita FROM citatorios WHERE mensaje_id=?',
        [$message['id']]);
    if ($citation) return $citation;

    // Older messages do not contain a meeting date or a selected child.
    $student=null;
    if ($message['destinatario_rol']==='alumno') {
        $student=['id'=>$message['destinatario_id'],'nombre'=>$message['destinatario']];
    } else {
        $children=rows("SELECT u.id,u.nombre FROM usuarios u JOIN vinculos v ON v.alumno_id=u.id
            WHERE v.padre_id=? AND u.rol='alumno' ORDER BY u.nombre",[$message['destinatario_id']]);
        if (count($children)===1) $student=$children[0];
    }
    $sender=one('SELECT nombre,rol FROM usuarios WHERE id=?',[$message['remitente_id']]);
    $teacher=$sender && $sender['rol']==='docente' ? $sender['nombre'] : null;
    if (!$teacher && $student) {
        $teachers=rows("SELECT DISTINCT u.nombre,u.id FROM usuarios u
            JOIN asignaciones a ON a.docente_id=u.id
            JOIN inscripciones i ON i.grupo_id=a.grupo_id
            WHERE i.alumno_id=? AND u.rol='docente'",[$student['id']]);
        if (count($teachers)===1) $teacher=$teachers[0]['nombre'];
    }
    return ['alumno_nombre'=>$student['nombre']??'Alumno por confirmar',
        'docente_nombre'=>$teacher??'Docente por confirmar','fecha_cita'=>null,'hora_cita'=>null];
}

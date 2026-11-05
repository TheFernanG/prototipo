<?php
declare(strict_types=1);

/** Printable citatorio using the school's compact, bordered letter format. */
function citatorioPdf(array $message): string {
    $encode=static function(string $text): string {
        $text=str_replace(["\r\n","\r","\t"],["\n","\n","    "],$text);
        $text=preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u','',$text) ?? '';
        $converted=iconv('UTF-8','Windows-1252//TRANSLIT',$text);
        return $converted===false ? '?' : $converted;
    };
    $escape=static function(string $text): string {
        return str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$text);
    };
    // Standard Helvetica glyph widths in Windows-1252, measured in 1/1000 em.
    $widths=[
        [
            761,761,761,761,761,761,761,761,761,761,761,761,761,761,761,761,
            761,761,761,761,761,761,761,761,761,761,761,761,761,761,761,761,
            278,278,355,556,556,889,667,191,333,333,389,584,278,333,278,278,
            556,556,556,556,556,556,556,556,556,556,278,278,584,584,584,556,
            1015,667,667,722,722,667,611,778,722,278,500,667,556,833,722,778,
            667,778,722,667,611,722,667,944,667,667,611,278,278,278,469,556,
            333,556,556,500,556,556,278,556,556,222,222,500,222,833,556,556,
            556,556,333,500,278,556,500,722,500,500,500,334,260,334,584,761,
            556,761,222,556,333,1000,556,556,333,1000,667,333,1000,761,611,761,
            761,222,222,333,333,350,556,1000,333,1000,500,333,944,761,500,667,
            278,333,556,556,556,556,260,556,333,737,370,556,584,333,737,333,
            400,584,333,333,333,556,537,278,333,333,365,556,834,834,834,611,
            667,667,667,667,667,667,1000,722,667,667,667,667,278,278,278,278,
            722,722,778,778,778,778,778,584,778,722,722,722,722,667,667,611,
            556,556,556,556,556,556,889,500,556,556,556,556,278,278,278,278,
            556,556,556,556,556,556,556,584,611,556,556,556,556,500,556,500,
        ],
        [
            761,761,761,761,761,761,761,761,761,761,761,761,761,761,761,761,
            761,761,761,761,761,761,761,761,761,761,761,761,761,761,761,761,
            278,333,474,556,556,889,722,238,333,333,389,584,278,333,278,278,
            556,556,556,556,556,556,556,556,556,556,333,333,584,584,584,611,
            975,722,722,722,722,667,611,778,722,278,556,722,611,833,722,778,
            667,778,722,667,611,722,667,944,667,667,611,333,278,333,584,556,
            333,556,611,556,611,556,333,611,611,278,278,556,278,889,611,611,
            611,611,389,556,333,611,556,778,556,556,500,389,280,389,584,761,
            556,761,278,556,500,1000,556,556,333,1000,667,333,1000,761,611,761,
            761,278,278,500,500,350,556,1000,333,1000,556,333,944,761,500,667,
            278,333,556,556,556,556,280,556,333,737,370,556,584,333,737,333,
            400,584,333,333,333,611,556,278,333,333,365,556,834,834,834,611,
            722,722,722,722,722,722,1000,722,667,667,667,667,278,278,278,278,
            722,722,778,778,778,778,778,584,778,722,722,722,722,667,667,611,
            556,556,556,556,556,556,889,556,556,556,556,556,278,278,278,278,
            611,611,611,611,611,611,611,584,611,611,611,611,611,556,611,556,
        ]
    ];
    $width=static function(string $text,int $size=10,bool $bold=false) use ($widths): float {
        $sum=0;
        foreach (str_split($text) as $character) $sum+=$widths[$bold?1:0][ord($character)];
        return $sum*$size/1000;
    };
    $textCommand=static function(string $text,float $x,int $y,int $size=10,bool $bold=false)
        use ($escape): string {
        $font=$bold?'F2':'F1';
        $xText=number_format($x,2,'.','');
        return "BT /$font $size Tf 0 g 1 0 0 1 $xText $y Tm (".$escape($text).") Tj ET\n";
    };
    $wrap=static function(string $text,int $size=10,int $available=512,bool $bold=false)
        use ($width): array {
        $lines=[];
        foreach (explode("\n",$text) as $paragraph) {
            $remaining=trim($paragraph);
            if ($remaining==='') { $lines[]=''; continue; }
            while ($width($remaining,$size,$bold)>$available) {
                $fit=0;
                $used=0;
                while ($fit<strlen($remaining)) {
                    $next=$width($remaining[$fit],$size,$bold);
                    if ($used+$next>$available) break;
                    $used+=$next;
                    $fit++;
                }
                $fit=max(1,$fit);
                $space=strrpos(substr($remaining,0,$fit+1),' ');
                $cut=$space!==false && $space>0 ? $space : $fit;
                $lines[]=rtrim(substr($remaining,0,$cut));
                $remaining=ltrim(substr($remaining,$cut));
            }
            if ($remaining!=='') $lines[]=$remaining;
        }
        return $lines;
    };
    $pages=[];
    $page='';
    $y=0;
    $startPage=static function() use (&$page,&$y,$textCommand,$width): void {
        $title='C I T A T O R I O';
        $page=$textCommand($title,(612-$width($title,18,true))/2,724,18,true);
        $y=692;
    };
    $finishPage=static function(int $bottom) use (&$page,&$pages): void {
        $page.="0 G 0.8 w 30 $bottom 552 ".(754-$bottom)." re S\n";
        $pages[]=$page;
    };
    $startPage();
    $addText=static function(string $text,bool $bold=false)
        use ($encode,$wrap,$textCommand,&$page,&$y,$startPage,$finishPage): void {
        foreach ($wrap($encode($text),10,512,$bold) as $line) {
            if ($y<112) {
                $finishPage(88);
                $startPage();
            }
            $page.=$textCommand($line,50,$y,10,$bold);
            $y-=16;
        }
    };
    $parent=$message['destinatario_rol']==='padre' ? ' '.$message['destinatario'] : '';
    $addText('SR. PADRE DE FAMILIA:'.$parent);
    $y-=5;
    $addText('POR MEDIO DEL PRESENTE SE SOLICITA SU PRESENCIA EN LA ESCUELA EL DÍA');
    if (!empty($message['fecha_cita'])) {
        $date=DateTimeImmutable::createFromFormat('!Y-m-d',$message['fecha_cita']);
        $months=[1=>'ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO',
            'JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'];
        $dateText=$date ? $date->format('d').' DE '.$months[(int)$date->format('n')].
            ' DE '.$date->format('Y') : 'FECHA POR CONFIRMAR';
        $time=!empty($message['hora_cita']) ? substr($message['hora_cita'],0,5).' HORAS' : 'HORA POR CONFIRMAR';
        $addText($dateText.', A LAS '.$time.'.',true);
    } else {
        $addText('FECHA Y HORA DE LA CITA POR CONFIRMAR.');
    }
    $y-=5;
    $addText('PARA TRATAR ASUNTOS RELACIONADOS CON LA EDUCACIÓN DE SU HIJO (A):');
    $addText($message['alumno_nombre'],true);
    $y-=5;
    $addText($message['asunto'],true);
    $addText($message['cuerpo']);

    $teacherLines=$wrap($encode($message['docente_nombre']),10,225);
    $signatureHeight=95+count($teacherLines)*14;
    if ($y-$signatureHeight<90) {
        $finishPage(max(88,$y-5));
        $startPage();
    }
    $y-=20;
    $thanks='A T E N T A M E N T E';
    $page.=$textCommand($thanks,(612-$width($thanks,10,true))/2,$y,10,true);
    $y-=32;
    foreach ($teacherLines as $line) {
        $page.=$textCommand($line,166-$width($line)/2,$y);
        $y-=14;
    }
    $lineY=$y+7;
    $page.="0 G 0.6 w 54 $lineY m 278 $lineY l S\n334 $lineY m 558 $lineY l S\n";
    $labelY=$lineY-16;
    $left=$encode('PROFR. (A) DE GRUPO');
    $right='FIRMA DEL PADRE DE FAMILIA';
    $page.=$textCommand($left,166-$width($left,9)/2,$labelY,9);
    $page.=$textCommand($right,446-$width($right,9)/2,$labelY,9);
    $finishPage($labelY-24);

    $objects=[];
    $objects[1]='<< /Type /Catalog /Pages 2 0 R >>';
    $objects[3]='<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
    $objects[4]='<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
    $kids=[];
    $total=count($pages);
    foreach ($pages as $index=>$content) {
        $pageId=5+$index*2;
        $streamId=$pageId+1;
        $kids[]="$pageId 0 R";
        $footer='CIT-'.str_pad((string)$message['id'],6,'0',STR_PAD_LEFT).
            ' | Emitido el '.date('d/m/Y',strtotime($message['creado'])).
            ' | Página '.($index+1).' de '.$total;
        $content.=$textCommand($encode($footer),50,40,8);
        $objects[$pageId]="<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792]
            /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >>
            /Contents $streamId 0 R >>";
        $objects[$streamId]='<< /Length '.strlen($content)." >>\nstream\n".$content."endstream";
    }
    $objects[2]='<< /Type /Pages /Count '.$total.' /Kids ['.implode(' ',$kids).'] >>';
    ksort($objects);
    $pdf="%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
    $offsets=[0];
    foreach ($objects as $id=>$object) {
        $offsets[$id]=strlen($pdf);
        $pdf.="$id 0 obj\n$object\nendobj\n";
    }
    $xref=strlen($pdf);
    $size=count($objects)+1;
    $pdf.="xref\n0 $size\n0000000000 65535 f \n";
    for ($id=1;$id<$size;$id++) $pdf.=sprintf("%010d 00000 n \n",$offsets[$id]);
    return $pdf."trailer\n<< /Size $size /Root 1 0 R >>\nstartxref\n$xref\n%%EOF\n";
}

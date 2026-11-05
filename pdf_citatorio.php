<?php
declare(strict_types=1);

/**
 * Build a printable Letter PDF using embedded PDF text commands and core fonts.
 * The monospaced body keeps arbitrary message lines within the printable area.
 */
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
    $textCommand=static function(string $text,int $x,int $y,int $size=10,string $font='F1')
        use ($escape): string {
        return "BT /$font $size Tf 0.12 0.20 0.23 rg 1 0 0 1 $x $y Tm (".
            $escape($text).") Tj ET\n";
    };
    $pages=[];
    $page='';
    $y=0;
    $startPage=static function() use (&$page,&$y,$textCommand,$encode): void {
        $page="0.03 0.40 0.30 rg 42 742 528 5 re f\n";
        $page.=$textCommand('CONECTA ESCUELA',42,714,14,'F2');
        $page.=$textCommand($encode('Citatorio escolar'),42,688,20,'F2');
        $y=658;
    };
    $startPage();
    $addLine=static function(string $line) use (&$page,&$pages,&$y,$startPage,$textCommand): void {
        if ($y<100) {
            $pages[]=$page;
            $startPage();
        }
        $page.=$textCommand($line,42,$y);
        $y-=15;
    };
    $addText=static function(string $text) use ($encode,$addLine): void {
        foreach (explode("\n",wordwrap($encode($text),86,"\n",true)) as $line) {
            $addLine($line);
        }
    };
    $addText('Folio: CIT-'.str_pad((string)$message['id'],6,'0',STR_PAD_LEFT));
    $addText('Fecha de emisión: '.date('d/m/Y H:i',strtotime($message['creado'])));
    $addText('Dirigido a: '.$message['destinatario']);
    $addText('Carácter: '.($message['destinatario_rol']==='padre'?'Padre, madre o tutor':'Alumno'));
    $addText('Remitente: '.$message['remitente']);
    $addText('Asunto: '.$message['asunto']);
    $addLine('');
    $addText('Se le solicita atender el siguiente citatorio:');
    $addLine('');
    $addText($message['cuerpo']);
    $addLine('');
    $addText('Revise en el mensaje los datos de la cita. Si falta algún dato,');
    $addText('confírmelo con la persona que envió este citatorio.');
    if ($y<245) {
        $pages[]=$page;
        $startPage();
    }
    $y-=30;
    $page.=$textCommand($encode('ACUSE DE RECIBIDO'),42,$y,12,'F2');
    $y-=27;
    $page.=$textCommand($encode('Nombre: ___________________________________________________________'),42,$y);
    $y-=42;
    $page.="0.3 0.4 0.4 RG 42 $y m 350 $y l S\n";
    $y-=17;
    $page.=$textCommand($encode($message['destinatario_rol']==='padre'?
        'Firma del padre, madre o tutor':'Firma del alumno'),42,$y);
    $y-=28;
    $page.=$textCommand($encode('Fecha de recepción: ____________________'),42,$y);
    $pages[]=$page;

    $objects=[];
    $objects[1]='<< /Type /Catalog /Pages 2 0 R >>';
    $objects[3]='<< /Type /Font /Subtype /Type1 /BaseFont /Courier /Encoding /WinAnsiEncoding >>';
    $objects[4]='<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
    $kids=[];
    $total=count($pages);
    foreach ($pages as $index=>$content) {
        $pageId=5+$index*2;
        $streamId=$pageId+1;
        $kids[]="$pageId 0 R";
        $content.=$textCommand($encode('Conecta Escuela | Citatorio '.$message['id'].
            ' | Página '.($index+1).' de '.$total),42,48,9);
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

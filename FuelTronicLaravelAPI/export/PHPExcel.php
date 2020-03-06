<?php

require_once dirname(__FILE__) . '/Database.php';

function PopulateExcel($sheet, $Sql, $title, $imagesql = "") {
    $sheet->setTitle($title);
    $result = DBQuery($Sql);
    $row = 1; // 1-based index
    $fields = $result->field_count;
    for ($i = 1; $i < $fields; $i++) {
        $finfo = $result->fetch_field_direct($i - 1);
        $sheet->setCellValueByColumnAndRow($i - 1, $row, $finfo->name);
    }
    $row++; // 1-based index
    $ColArray = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW',
        'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO'];
    while ($row_data = $result->fetch_assoc()) {
        $col = 0;
        echo date('H:i:s'), " Row #$row of $title", EOL;
        flush();
        foreach ($row_data as $key => $value) {
            $sheet->setCellValueByColumnAndRow($col, $row, $value);
            $col++;
        }
        if ($imagesql != "") {
            $imageresult = DBQuery(str_replace('{learnerid}', $row_data['LearnerID'], $imagesql));
            $j = 0;
            $row++;
            $height = 0;
            while ($image_row_data = $imageresult->fetch_assoc()) {
                $src = str_replace('data:image/png;base64', '', $image_row_data['data']);
                // Load image into memory
                $image = imagecreatefromstring(base64_decode($src));
                imagesavealpha($image, true);
                //list($width, $height, $type, $attr) = getimagesize($image);
                
                $height = (ImageSY($image) > $height ? ImageSY($image) : $height);
                $drawing = new PHPExcel_Worksheet_MemoryDrawing();
                $drawing->setName("Images");
                $drawing->setWorksheet($sheet);
                $drawing->setImageResource($image);
                $drawing->setCoordinates($ColArray[$j] . $row);
                $drawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_PNG);
                $drawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
                $sheet->getRowDimension($row)->setRowHeight($height);
                $sheet->getColumnDimension($ColArray[$j])->setWidth(ImageSX($image));
                $j++;
            }
        }
        $row++;
    }
}

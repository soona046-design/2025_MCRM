<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use SplTempFileObject;

class ExportService
{
    /**
     * 데이터를 CSV 파일로 내보냅니다.
     *
     * @param Collection $data 내보낼 데이터
     * @param array $headers CSV 헤더
     * @param string $filename 파일 이름
     * @return string 파일 경로
     */
    public function toCsv(Collection $data, array $headers, string $filename): string
    {
        $csv = Writer::createFromFileObject(new SplTempFileObject());
        
        // UTF-8 BOM 추가 (Excel에서 한글 깨짐 방지)
        $csv->setOutputBOM(Writer::BOM_UTF8);
        
        // 헤더 추가
        $csv->insertOne($headers);
        
        // 데이터 추가
        $csv->insertAll($data->toArray());
        
        // 파일 저장
        $path = "exports/{$filename}";
        Storage::put($path, $csv->toString());
        
        return $path;
    }

    /**
     * 데이터를 Excel 파일로 내보냅니다.
     *
     * @param Collection $data 내보낼 데이터
     * @param array $headers Excel 헤더
     * @param string $filename 파일 이름
     * @return string 파일 경로
     */
    public function toExcel(Collection $data, array $headers, string $filename): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // 헤더 추가
        $column = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($column++, 1, $header);
        }
        
        // 데이터 추가
        $row = 2;
        foreach ($data as $item) {
            $column = 1;
            foreach ($item as $value) {
                $sheet->setCellValueByColumnAndRow($column++, $row, $value);
            }
            $row++;
        }
        
        // 파일 저장
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $path = "exports/{$filename}";
        $fullPath = Storage::path($path);
        $writer->save($fullPath);
        
        return $path;
    }

    /**
     * 내보내기 파일을 삭제합니다.
     *
     * @param string $path 파일 경로
     * @return bool 삭제 성공 여부
     */
    public function deleteExportFile(string $path): bool
    {
        return Storage::delete($path);
    }
}

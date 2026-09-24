<?php

namespace App\Services\Tools;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use RuntimeException;
use ZipArchive;

/**
 * Writes rows directly to worksheet XML so export size
 * does not determine PHP cell memory.
 */
final class ExcelFilterWorkbookWriter
{
    public function write(string $path, array $headers, iterable $rows): int
    {
        $sheetPath = tempnam(dirname($path), 'excel-sheet-');

        if ($sheetPath === false || ($stream = fopen($sheetPath, 'wb')) === false) {
            throw new RuntimeException(__('The Excel export could not be prepared.'));
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $rowNumber = 1;

        try {
            fwrite(
                $stream,
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            );

            fwrite(
                $stream,
                '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            );

            // Freeze header row
            fwrite(
                $stream,
                '<sheetViews>'
                    .'<sheetView workbookViewId="0">'
                        .'<pane '
                            .'ySplit="1" '
                            .'topLeftCell="A2" '
                            .'activePane="bottomLeft" '
                            .'state="frozen"'
                        .'/>'
                    .'</sheetView>'
                .'</sheetViews>'
            );

            // Column widths
            fwrite($stream, '<cols>');

            foreach ($headers as $index => $header) {
                $column = $index + 1;
                $width = min(48, max(14, mb_strlen($header) + 3));

                fwrite(
                    $stream,
                    "<col min=\"{$column}\" "
                    ."max=\"{$column}\" "
                    ."width=\"{$width}\" "
                    .'customWidth="1"/>'
                );
            }

            fwrite($stream, '</cols>');

            // Worksheet data
            fwrite($stream, '<sheetData>');

            // Header
            $this->writeRow(
                $stream,
                $rowNumber,
                $headers,
                true
            );

            // Data
            foreach ($rows as $row) {
                $this->writeRow(
                    $stream,
                    ++$rowNumber,
                    $row,
                    false
                );
            }

            fwrite(
                $stream,
                '</sheetData>'
                ."<autoFilter ref=\"A1:{$lastColumn}{$rowNumber}\"/>"
                .'</worksheet>'
            );
        } finally {
            fclose($stream);
        }

        if ($rowNumber === 1) {
            unlink($sheetPath);

            throw new RuntimeException(
                __('There are no matching rows to export.')
            );
        }

        $zip = new ZipArchive;

        if (
            $zip->open(
                $path,
                ZipArchive::CREATE | ZipArchive::OVERWRITE
            ) !== true
        ) {
            unlink($sheetPath);

            throw new RuntimeException(
                __('The Excel export could not be created.')
            );
        }

        try {
            /*
            |--------------------------------------------------------------------------
            | Content Types
            |--------------------------------------------------------------------------
            */

            $zip->addFromString(
                '[Content_Types].xml',
                '<?xml version="1.0" encoding="UTF-8"?>'
                .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                    .'<Default '
                        .'Extension="rels" '
                        .'ContentType="application/vnd.openxmlformats-package.relationships+xml"'
                    .'/>'
                    .'<Default '
                        .'Extension="xml" '
                        .'ContentType="application/xml"'
                    .'/>'
                    .'<Override '
                        .'PartName="/xl/workbook.xml" '
                        .'ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"'
                    .'/>'
                    .'<Override '
                        .'PartName="/xl/worksheets/sheet1.xml" '
                        .'ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"'
                    .'/>'
                    .'<Override '
                        .'PartName="/xl/styles.xml" '
                        .'ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"'
                    .'/>'
                .'</Types>'
            );

            /*
            |--------------------------------------------------------------------------
            | Root Relationships
            |--------------------------------------------------------------------------
            */

            $zip->addFromString(
                '_rels/.rels',
                '<?xml version="1.0" encoding="UTF-8"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                    .'<Relationship '
                        .'Id="rId1" '
                        .'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" '
                        .'Target="xl/workbook.xml"'
                    .'/>'
                .'</Relationships>'
            );

            /*
            |--------------------------------------------------------------------------
            | Workbook
            |--------------------------------------------------------------------------
            */

            $zip->addFromString(
                'xl/workbook.xml',
                '<?xml version="1.0" encoding="UTF-8"?>'
                .'<workbook '
                    .'xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
                    .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                    .'<sheets>'
                        .'<sheet '
                            .'name="Filtered data" '
                            .'sheetId="1" '
                            .'r:id="rId1"'
                        .'/>'
                    .'</sheets>'
                .'</workbook>'
            );

            /*
            |--------------------------------------------------------------------------
            | Workbook Relationships
            |--------------------------------------------------------------------------
            */

            $zip->addFromString(
                'xl/_rels/workbook.xml.rels',
                '<?xml version="1.0" encoding="UTF-8"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                    .'<Relationship '
                        .'Id="rId1" '
                        .'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" '
                        .'Target="worksheets/sheet1.xml"'
                    .'/>'
                    .'<Relationship '
                        .'Id="rId2" '
                        .'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" '
                        .'Target="styles.xml"'
                    .'/>'
                .'</Relationships>'
            );

            /*
            |--------------------------------------------------------------------------
            | Number Formats
            |--------------------------------------------------------------------------
            */

            $numberFormats = '';
            $numberStyles = '';

            for ($decimals = 0; $decimals <= 10; $decimals++) {
                $formatId = 164 + $decimals;

                $format = '#,##0'
                    .($decimals > 0
                        ? '.'.str_repeat('0', $decimals)
                        : '');

                $numberFormats .=
                    '<numFmt '
                    ."numFmtId=\"{$formatId}\" "
                    ."formatCode=\"{$format}\""
                    .'/>';

                $numberStyles .=
                    '<xf '
                    ."numFmtId=\"{$formatId}\" "
                    .'fontId="0" '
                    .'fillId="0" '
                    .'borderId="0" '
                    .'xfId="0" '
                    .'applyNumberFormat="1"'
                    .'/>';
            }

            /*
            |--------------------------------------------------------------------------
            | Excel Styles
            |--------------------------------------------------------------------------
            |
            | fillId 0 = None
            | fillId 1 = gray125
            | fillId 2 = Header blue #2563EB
            |
            | fontId 0 = Normal
            | fontId 1 = Header: white + bold
            |
            */

            $zip->addFromString(
                'xl/styles.xml',
                '<?xml version="1.0" encoding="UTF-8"?>'

                .'<styleSheet '
                    .'xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'

                /*
                 * Number formats
                 */
                .'<numFmts count="11">'
                    .$numberFormats
                .'</numFmts>'

                /*
                 * Fonts
                 */
                .'<fonts count="2">'

                    // Normal font
                    .'<font>'
                        .'<sz val="11"/>'
                        .'<name val="Aptos"/>'
                    .'</font>'

                    // Header font
                    .'<font>'
                        .'<b/>'
                        .'<color rgb="FFFFFFFF"/>'
                        .'<sz val="11"/>'
                        .'<name val="Aptos"/>'
                    .'</font>'

                .'</fonts>'

                /*
                 * Fills
                 */
                .'<fills count="3">'

                    // Required default fill
                    .'<fill>'
                        .'<patternFill patternType="none"/>'
                    .'</fill>'

                    // Required Excel gray125 fill
                    .'<fill>'
                        .'<patternFill patternType="gray125"/>'
                    .'</fill>'

                    // Header blue #2563EB
                    .'<fill>'
                        .'<patternFill patternType="solid">'
                            .'<fgColor rgb="FF2563EB"/>'
                            .'<bgColor indexed="64"/>'
                        .'</patternFill>'
                    .'</fill>'

                .'</fills>'

                /*
                 * Borders
                 */
                .'<borders count="1">'
                    .'<border>'
                        .'<left/>'
                        .'<right/>'
                        .'<top/>'
                        .'<bottom/>'
                        .'<diagonal/>'
                    .'</border>'
                .'</borders>'

                /*
                 * Base style
                 */
                .'<cellStyleXfs count="1">'
                    .'<xf '
                        .'numFmtId="0" '
                        .'fontId="0" '
                        .'fillId="0" '
                        .'borderId="0"'
                    .'/>'
                .'</cellStyleXfs>'

                /*
                 * Cell styles
                 *
                 * Style 0 = normal
                 * Style 1 = header
                 * Style 2-12 = numeric formats
                 */
                .'<cellXfs count="13">'

                    // Style 0 - Normal
                    .'<xf '
                        .'numFmtId="0" '
                        .'fontId="0" '
                        .'fillId="0" '
                        .'borderId="0" '
                        .'xfId="0"'
                    .'/>'

                    // Style 1 - Header
                    .'<xf '
                        .'numFmtId="0" '
                        .'fontId="1" '
                        .'fillId="2" '
                        .'borderId="0" '
                        .'xfId="0" '
                        .'applyFont="1" '
                        .'applyFill="1"'
                    .'/>'

                    // Numeric styles
                    .$numberStyles

                .'</cellXfs>'

                /*
                 * Named styles
                 */
                .'<cellStyles count="1">'
                    .'<cellStyle '
                        .'name="Normal" '
                        .'xfId="0" '
                        .'builtinId="0"'
                    .'/>'
                .'</cellStyles>'

                .'</styleSheet>'
            );

            /*
            |--------------------------------------------------------------------------
            | Worksheet
            |--------------------------------------------------------------------------
            */

            $zip->addFile(
                $sheetPath,
                'xl/worksheets/sheet1.xml'
            );
        } finally {
            $zip->close();
            unlink($sheetPath);
        }

        return $rowNumber - 1;
    }

    /**
     * Write one Excel worksheet row directly to XML.
     */
    private function writeRow(
        $stream,
        int $number,
        array $values,
        bool $header
    ): void {
        fwrite(
            $stream,
            "<row r=\"{$number}\">"
        );

        foreach (array_values($values) as $index => $value) {
            $cell =
                Coordinate::stringFromColumnIndex($index + 1)
                .$number;

            /*
             * Numeric value
             */
            if (
                ! $header
                && is_array($value)
                && $value['type'] === 'number'
            ) {
                $style = 2 + $value['decimals'];

                fwrite(
                    $stream,
                    '<c '
                    ."r=\"{$cell}\" "
                    .'t="n" '
                    ."s=\"{$style}\""
                    .'>'
                    ."<v>{$value['value']}</v>"
                    .'</c>'
                );

                continue;
            }

            /*
             * String / header value
             */
            $safe = htmlspecialchars(
                preg_replace(
                    '/[\x00-\x08\x0B\x0C\x0E-\x1F]/u',
                    '',
                    (string) (
                        is_array($value)
                            ? $value['value']
                            : $value
                    )
                ) ?? '',
                ENT_XML1 | ENT_QUOTES,
                'UTF-8'
            );

            // Header uses style 1
            $style = $header
                ? ' s="1"'
                : '';

            fwrite(
                $stream,
                '<c '
                ."r=\"{$cell}\" "
                .'t="inlineStr"'
                ."{$style}"
                .'>'
                .'<is>'
                    ."<t xml:space=\"preserve\">{$safe}</t>"
                .'</is>'
                .'</c>'
            );
        }

        fwrite(
            $stream,
            '</row>'
        );
    }
}

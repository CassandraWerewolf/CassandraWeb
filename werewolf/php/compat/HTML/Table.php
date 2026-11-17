<?php
// Minimal replacement for PEAR HTML_Table (abandoned; does not run on PHP 8).
// Implements only the methods this app uses, with HTML_Table's behavior:
// rows and columns are 0-based, empty cells are filled with &nbsp;, row/column
// attributes apply to each existing cell, and colspan/rowspan cells hide the
// cells they cover. Loaded via include_path (see php/Dockerfile).

class HTML_Table
{
    private $attributes;
    private $cells = array();   // [row][col] => array('type' => 'td'|'th', 'contents' => string, 'attr' => array)
    private $rowAttr = array(); // [row] => attributes placed on <tr> (when $inTR is true)
    private $rows = 0;
    private $cols = 0;

    public function __construct($attributes = null, $tabOffset = 0, $useTGroups = false)
    {
        $this->attributes = self::parseAttributes($attributes);
    }

    public function getRowCount()
    {
        return $this->rows;
    }

    public function setCellContents($row, $col, $contents, $type = 'TD')
    {
        $this->cell($row, $col);
        $this->cells[$row][$col]['contents'] = $contents;
        $this->cells[$row][$col]['type'] = strtolower($type);
    }

    public function setHeaderContents($row, $col, $contents, $attributes = null)
    {
        $this->setCellContents($row, $col, $contents, 'TH');
        if ($attributes !== null) {
            $this->setCellAttributes($row, $col, $attributes);
        }
    }

    public function setCellAttributes($row, $col, $attributes)
    {
        $this->cell($row, $col);
        $this->cells[$row][$col]['attr'] = self::parseAttributes($attributes);
    }

    public function updateCellAttributes($row, $col, $attributes)
    {
        $this->cell($row, $col);
        $this->cells[$row][$col]['attr'] = array_merge(
            $this->cells[$row][$col]['attr'], self::parseAttributes($attributes));
    }

    public function addRow($contents = null, $attributes = null, $type = 'TD', $inTR = false)
    {
        $row = $this->rows;
        foreach ((array) $contents as $col => $content) {
            $this->setCellContents($row, $col, $content, $type);
        }
        $this->cell($row, 0);
        if ($attributes !== null) {
            $this->setRowAttributes($row, $attributes, $inTR);
        }
        return $row;
    }

    public function addCol($contents = null, $attributes = null, $type = 'TD')
    {
        $col = $this->cols;
        foreach ((array) $contents as $row => $content) {
            $this->setCellContents($row, $col, $content, $type);
        }
        $this->cell(0, $col);
        if ($attributes !== null) {
            $this->updateColAttributes($col, $attributes);
        }
        return $col;
    }

    public function setRowType($row, $type)
    {
        for ($col = 0; $col < $this->cols; $col++) {
            $this->cell($row, $col);
            $this->cells[$row][$col]['type'] = strtolower($type);
        }
    }

    public function setRowAttributes($row, $attributes, $inTR = false)
    {
        if ($inTR) {
            $this->rowAttr[$row] = self::parseAttributes($attributes);
            return;
        }
        for ($col = 0; $col < $this->cols; $col++) {
            $this->setCellAttributes($row, $col, $attributes);
        }
    }

    public function updateRowAttributes($row, $attributes, $inTR = false)
    {
        if ($inTR) {
            $this->rowAttr[$row] = array_merge($this->rowAttr[$row] ?? array(),
                self::parseAttributes($attributes));
            return;
        }
        for ($col = 0; $col < $this->cols; $col++) {
            $this->updateCellAttributes($row, $col, $attributes);
        }
    }

    public function updateColAttributes($col, $attributes)
    {
        for ($row = 0; $row < $this->rows; $row++) {
            $this->updateCellAttributes($row, $col, $attributes);
        }
    }

    public function toHTML()
    {
        $html = '<table' . self::attrString($this->attributes) . ">\n";
        $covered = array(); // cells hidden by a colspan/rowspan
        for ($row = 0; $row < $this->rows; $row++) {
            $html .= '<tr' . self::attrString($this->rowAttr[$row] ?? array()) . ">\n";
            for ($col = 0; $col < $this->cols; $col++) {
                if (isset($covered[$row][$col])) {
                    continue;
                }
                $cell = $this->cells[$row][$col] ?? array('type' => 'td', 'contents' => null, 'attr' => array());
                $colspan = max(1, (int) ($cell['attr']['colspan'] ?? 1));
                $rowspan = max(1, (int) ($cell['attr']['rowspan'] ?? 1));
                for ($r = $row; $r < $row + $rowspan; $r++) {
                    for ($c = $col; $c < $col + $colspan; $c++) {
                        if ($r != $row || $c != $col) {
                            $covered[$r][$c] = true;
                        }
                    }
                }
                $contents = ($cell['contents'] === null || $cell['contents'] === '') ? '&nbsp;' : $cell['contents'];
                $tag = $cell['type'] === 'th' ? 'th' : 'td';
                $html .= "<$tag" . self::attrString($cell['attr']) . ">$contents</$tag>\n";
            }
            $html .= "</tr>\n";
        }
        return $html . "</table>\n";
    }

    public function display()
    {
        print $this->toHTML();
    }

    // Make sure a cell exists and the table is at least that big.
    private function cell($row, $col)
    {
        if (!isset($this->cells[$row][$col])) {
            $this->cells[$row][$col] = array('type' => 'td', 'contents' => null, 'attr' => array());
        }
        $this->rows = max($this->rows, $row + 1);
        $this->cols = max($this->cols, $col + 1);
    }

    // Accepts an array (name => value) or a string like "class='x' width=100 nowrap".
    private static function parseAttributes($attributes)
    {
        if (is_array($attributes)) {
            return array_change_key_case($attributes, CASE_LOWER);
        }
        $result = array();
        if (!is_string($attributes) || $attributes === '') {
            return $result;
        }
        preg_match_all('/([\w:-]+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))|([\w:-]+)/', $attributes, $m, PREG_SET_ORDER);
        foreach ($m as $match) {
            if (!empty($match[5])) {
                $result[strtolower($match[5])] = $match[5];
            } else {
                $value = $match[2] !== '' ? $match[2] : ($match[3] !== '' ? $match[3] : ($match[4] ?? ''));
                $result[strtolower($match[1])] = $value;
            }
        }
        return $result;
    }

    private static function attrString($attributes)
    {
        $out = '';
        foreach ($attributes as $name => $value) {
            $out .= ' ' . $name . '="' . htmlspecialchars((string) $value, ENT_COMPAT) . '"';
        }
        return $out;
    }
}

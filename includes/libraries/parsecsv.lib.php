<?php

class parseCSV {

    /*
    Class: parseCSV v0.4.3 beta
    https://github.com/parsecsv/parsecsv-for-php

    Fully conforms to the specifications lined out on wikipedia:
    - http://en.wikipedia.org/wiki/Comma-separated_values

    Based on the concept of Ming Hong Ng's CsvFileParser class:
    - http://minghong.blogspot.com/2006/07/csv-parser-for-php.html


    (The MIT license)

    Copyright (c) 2014 Jim Myhrberg.

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.


    Code Examples
    ----------------
    # general usage
    $csv = new parseCSV('data.csv');
    print_r($csv->data);
    ----------------
    # tab delimited, and encoding conversion
    $csv = new parseCSV();
    $csv->encoding('UTF-16', 'UTF-8');
    $csv->delimiter = "\t";
    $csv->parse('data.tsv');
    print_r($csv->data);
    ----------------
    # auto-detect delimiter character
    $csv = new parseCSV();
    $csv->auto('data.csv');
    print_r($csv->data);
    ----------------
    # modify data in a csv file
    $csv = new parseCSV();
    $csv->sort_by = 'id';
    $csv->parse('data.csv');
    # "4" is the value of the "id" column of the CSV row
    $csv->data[4] = array('firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@doe.com');
    $csv->save();
    ----------------
    # add row/entry to end of CSV file
    #  - only recommended when you know the extact sctructure of the file
    $csv = new parseCSV();
    $csv->save('data.csv', array(array('1986', 'Home', 'Nowhere', '')), true);
    ----------------
    # convert 2D array to csv data and send headers
    # to browser to treat output as a file and download it
    $csv = new parseCSV();
    $csv->output('movies.csv', $array, array('field 1', 'field 2'), ',');
    ----------------
     */

    /**
     * Configuration
     * - set these options with $object->var_name = 'value';
     */

    /**
     * Heading
     * Use first line/entry as field names
     *
     * @access public
     * @var bool
     */
    public $heading = true;

    /**
     * Fields
     * Override field names
     *
     * @access public
     * @var array
     */
    public $fields = array();

    /**
     * Sort By
     * Sort csv by this field
     *
     * @access public
     * @var string
     */
    public $sort_by = null;

    /**
     * Sort Reverse
     * Reverse the sort function
     *
     * @access public
     * @var bool
     */
    public $sort_reverse = false;

    /**
     * Sort Type
     * Sort behavior passed to sort methods
     *
     * regular = SORT_REGULAR
     * numeric = SORT_NUMERIC
     * string  = SORT_STRING
     *
     * @access public
     * @var string
     */
    public $sort_type = null;

    /**
     * Delimiter
     * Delimiter character
     *
     * @access public
     * @var string
     */
    public $delimiter = ',';

    /**
     * Enclosure
     * Enclosure character
     *
     * @access public
     * @var string
     */
    public $enclosure = '"';

    /**
     * Enclose All
     * Force enclosing all columns
     *
     * @access public
     * @var bool
     */
    public $enclose_all = false;

    /**
     * Conditions
     * Basic SQL-Like conditions for row matching
     *
     * @access public
     * @var string
     */
    public $conditions = null;

    /**
     * Offset
     * Number of rows to ignore from beginning of data
     *
     * @access public
     * @var int
     */
    public $offset = null;

    /**
     * Limit
     * Limits the number of returned rows to the specified amount
     *
     * @access public
     * @var int
     */
    public $limit = null;

    /**
     * Auto Depth
     * Number of rows to analyze when attempting to auto-detect delimiter
     *
     * @access public
     * @var int
     */
    public $auto_depth = 15;

    /**
     * Auto Non Charts
     * Characters that should be ignored when attempting to auto-detect delimiter
     *
     * @access public
     * @var string
     */
    public $auto_non_chars = "a-zA-Z0-9\n\r";

    /**
     * Auto Preferred
     * preferred delimiter characters, only used when all filtering method
     * returns multiple possible delimiters (happens very rarely)
     *
     * @access public
     * @var string
     */
    public $auto_preferred = ",;\t.:|";

    /**
     * Convert Encoding
     * Should we convert the csv encoding?
     *
     * @access public
     * @var bool
     */
    public $convert_encoding = false;

    /**
     * Input Encoding
     * Set the input encoding
     *
     * @access public
     * @var string
     */
    public $input_encoding = 'ISO-8859-1';

    /**
     * Output Encoding
     * Set the output encoding
     *
     * @access public
     * @var string
     */
    public $output_encoding = 'ISO-8859-1';

    /**
     * Linefeed
     * Line feed characters used by unparse, save, and output methods
     *
     * @access public
     * @var string
     */
    public $linefeed = "\r";

    /**
     * Output Delimiter
     * Sets the output delimiter used by the output method
     *
     * @access public
     * @var string
     */
    public $output_delimiter = ',';

    /**
     * Output filename
     * Sets the output filename
     *
     * @access public
     * @var string
     */
    public $output_filename = 'data.csv';

    /**
     * Keep File Data
     * keep raw file data in memory after successful parsing (useful for debugging)
     *
     * @access public
     * @var bool
     */
    public $keep_file_data = false;

    /**
     * Internal variables
     */

    /**
     * File
     * Current Filename
     *
     * @access public
     * @var string
     */
    public $file;

    /**
     * File Data
     * Current file data
     *
     * @access public
     * @var string
     */
    public $file_data;

    /**
     * Error
     * Contains the error code if one occured
     *
     * 0 = No errors found. Everything should be fine :)
     * 1 = Hopefully correctable syntax error was found.
     * 2 = Enclosure character (double quote by default)
     *     was found in non-enclosed field. This means
     *     the file is either corrupt, or does not
     *     standard CSV formatting. Please validate
     *     the parsed data yourself.
     *
     * @access public
     * @var int
     */
    public $error = 0;

    /**
     * Error Information
     * Detailed error information
     *
     * @access public
     * @var array
     */
    public $error_info = array();

    /**
     * Titles
     * CSV titles if they exists
     *
     * @access public
     * @var array
     */
    public $titles = array();

    /**
     * Data
     * Two dimensional array of CSV data
     *
     * @access public
     * @var array
     */
    public $data = array();

    /**
     * Constructor
     * Class constructor
     *
     * @access public
     * @param  [string]  input      The CSV string or a direct filepath
     * @param  [integer] offset     Number of rows to ignore from the beginning of  the data
     * @param  [integer] limit      Limits the number of returned rows to specified amount
     * @param  [string]  conditions Basic SQL-like conditions for row matching
     */
    public function __construct($input = null, $offset = null, $limit = null, $conditions = null, $keep_file_data = null) {
        if (!is_null($offset)) {
            $this->offset = $offset;
        }

        if (!is_null($limit)) {
            $this->limit = $limit;
        }

        if (!is_null($conditions)) {
            $this->conditions = $conditions;
        }

        if (!is_null($keep_file_data)) {
            $this->keep_file_data = $keep_file_data;
        }

        if (!empty($input)) {
            $this->parse($input);
        }
    }

    // ==============================================
    // ----- [ Main Functions ] ---------------------
    // ==============================================

    /**
     * Parse
     * Parse a CSV file or string
     *
     * @access public
     * @param  [string]  input      The CSV string or a direct filepath
     * @param  [integer] offset     Number of rows to ignore from the beginning of  the data
     * @param  [integer] limit      Limits the number of returned rows to specified amount
     * @param  [string]  conditions Basic SQL-like conditions for row matching
     *
     * @return [bool]
     */
    public function parse($input = null, $offset = null, $limit = null, $conditions = null) {
        if (is_null($input)) {
            $input = $this->file;
        }

        if (!empty($input)) {
            if (!is_null($offset)) {
                $this->offset = $offset;
            }

            if (!is_null($limit)) {
                $this->limit = $limit;
            }

            if (!is_null($conditions)) {
                $this->conditions = $conditions;
            }

            if (is_readable($input)) {
                $this->data = $this->parse_file($input);
            } else {
                $this->file_data = &$input;
                $this->data = $this->parse_string();
            }

            if ($this->data === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Save
     * Save changes, or write a new file and/or data
     *
     * @access public
     * @param  [string] $file   File location to save to
     * @param  [array]  $data   2D array of data
     * @param  [bool]   $append Append current data to end of target CSV, if file exists
     * @param  [array]  $fields Field names
     *
     * @return [bool]
     */
    public function save($file = null, $data = array(), $append = false, $fields = array()) {
        if (empty($file)) {
            $file = &$this->file;
        }

        $mode = ($append) ? 'at' : 'wt';
        $is_php = (preg_match('/\.php$/i', $file)) ? true : false;

        return $this->_wfile($file, $this->unparse($data, $fields, $append, $is_php), $mode);
    }

    /**
     * Output
     * Generate a CSV based string for output.
     *
     * @access public
     * @param  [string] $filename  If specified, headers and data will be output directly to browser as a downloable file
     * @param  [array]  $data      2D array with data
     * @param  [array]  $fields    Field names
     * @param  [type]   $delimiter delimiter used to separate data
     *
     * @return [string]
     */
    public function output($filename = null, $data = array(), $fields = array(), $delimiter = null) {
        if (empty($filename)) {
            $filename = $this->output_filename;
        }

        if ($delimiter === null) {
            $delimiter = $this->output_delimiter;
        }

        $data = $this->unparse($data, $fields, null, null, $delimiter);

        if (!is_null($filename)) {
            header('Content-type: application/csv');
            header('Content-Length: ' . strlen($data));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Content-Disposition: attachment; filename="' . $filename . '"; modification-date="' . date('r') . '";');

            echo $data;
        }

        return $data;
    }

    /**
     * Encoding
     * Convert character encoding
     *
     * @access public
     * @param  [string] $input  Input character encoding, uses default if left blank
     * @param  [string] $output Output character encoding, uses default if left blank
     */
    public function encoding($input = null, $output = null) {
        $this->convert_encoding = true;
        if (!is_null($input)) {
            $this->input_encoding = $input;
        }

        if (!is_null($output)) {
            $this->output_encoding = $output;
        }
    }

    /**
     * Auto
     * Auto-Detect Delimiter: Find delimiter by analyzing a specific number of
     * rows to determine most probable delimiter character
     *
     * @access public
     * @param  [string] $file         Local CSV file
     * @param  [bool]   $parse        True/false parse file directly
     * @param  [int]    $search_depth Number of rows to analyze
     * @param  [string] $preferred    Preferred delimiter characters
     * @param  [string] $enclosure    Enclosure character, default is double quote (").
     *
     * @return [string]
     */
    public function auto($file = null, $parse = true, $search_depth = null, $preferred = null, $enclosure = null) {
        if (is_null($file)) {
            $file = $this->file;
        }

        if (empty($search_depth)) {
            $search_depth = $this->auto_depth;
        }

        if (is_null($enclosure)) {
            $enclosure = $this->enclosure;
        }

        if (is_null($preferred)) {
            $preferred = $this->auto_preferred;
        }

        if (empty($this->file_data)) {
            if ($this->_check_data($file)) {
                $data = &$this->file_data;
            } else {
                return false;
            }
        } else {
            $data = &$this->file_data;
        }

        $chars = array();
        $strlen = strlen($data);
        $enclosed = false;
        $n = 1;
        $to_end = true;

        // walk specific depth finding posssible delimiter characters
        for ($i = 0; $i < $strlen; $i++) {
            $ch = $data{$i};
            $nch = (isset($data{$i + 1})) ? $data{$i + 1} : false;
            $pch = (isset($data{$i - 1})) ? $data{$i - 1} : false;

            // open and closing quotes
            if ($ch == $enclosure) {
                if (!$enclosed || $nch != $enclosure) {
                    $enclosed = ($enclosed) ? false : true;
                } elseif ($enclosed) {
                    $i++;
                }

                // end of row
            } elseif (($ch == "\n" && $pch != "\r" || $ch == "\r") && !$enclosed) {
                if ($n >= $search_depth) {
                    $strlen = 0;
                    $to_end = false;
                } else {
                    $n++;
                }

                // count character
            } elseif (!$enclosed) {
                if (!preg_match('/[' . preg_quote($this->auto_non_chars, '/') . ']/i', $ch)) {
                    if (!isset($chars[$ch][$n])) {
                        $chars[$ch][$n] = 1;
                    } else {
                        $chars[$ch][$n]++;
                    }
                }
            }
        }

        // filtering
        $depth = ($to_end) ? $n - 1 : $n;
        $filtered = array();
        foreach ($chars as $char => $value) {
            if ($match = $this->_check_count($char, $value, $depth, $preferred)) {
                $filtered[$match] = $char;
            }
        }

        // capture most probable delimiter
        ksort($filtered);
        $this->delimiter = reset($filtered);

        // parse data
        if ($parse) {
            $this->data = $this->parse_string();
        }

        return $this->delimiter;
    }

    // ==============================================
    // ----- [ Core Functions ] ---------------------
    // ==============================================

    /**
     * Parse File
     * Read file to string and call parse_string()
     *
     * @access public
     *
     * @param  [string] $file Local CSV file
     *
     * @return [array|bool]
     */
    public function parse_file($file = null) {
        if (is_null($file)) {
            $file = $this->file;
        }

        if (empty($this->file_data)) {
            $this->load_data($file);
        }

        return (!empty($this->file_data)) ? $this->parse_string() : false;
    }

    /**
     * Parse CSV strings to arrays
     *
     * @access public
     * @param   data   CSV string
     *
     * @return  2D array with CSV data, or false on failure
     */
    public function parse_string($data = null) {
        if (empty($data)) {
            if ($this->_check_data()) {
                $data = &$this->file_data;
            } else {
                return false;
            }
        }

        $white_spaces = str_replace($this->delimiter, '', " \t\x0B\0");

        $rows = array();
        $row = array();
        $row_count = 0;
        $current = '';
        $head = (!empty($this->fields)) ? $this->fields : array();
        $col = 0;
        $enclosed = false;
        $was_enclosed = false;
        $strlen = strlen($data);

        // force the parser to process end of data as a character (false) when
        // data does not end with a line feed or carriage return character.
        $lch = $data{$strlen - 1};
        if ($lch != "\n" && $lch != "\r") {
            $strlen++;
        }

        // walk through each character
        for ($i = 0; $i < $strlen; $i++) {
            $ch = (isset($data{$i})) ? $data{$i} : false;
            $nch = (isset($data{$i + 1})) ? $data{$i + 1} : false;
            $pch = (isset($data{$i - 1})) ? $data{$i - 1} : false;

            // open/close quotes, and inline quotes
            if ($ch == $this->enclosure) {
                if (!$enclosed) {
                    if (ltrim($current, $white_spaces) == '') {
                        $enclosed = true;
                        $was_enclosed = true;
                    } else {
                        $this->error = 2;
                        $error_row = count($rows) + 1;
                        $error_col = $col + 1;
                        if (!isset($this->error_info[$error_row . '-' . $error_col])) {
                            $this->error_info[$error_row . '-' . $error_col] = array(
                                'type' => 2,
                                'info' => 'Syntax error found on row ' . $error_row . '. Non-enclosed fields can not contain double-quotes.',
                                'row' => $error_row,
                                'field' => $error_col,
                                'field_name' => (!empty($head[$col])) ? $head[$col] : null,
                            );
                        }

                        $current .= $ch;
                    }
                } elseif ($nch == $this->enclosure) {
                    $current .= $ch;
                    $i++;
                } elseif ($nch != $this->delimiter && $nch != "\r" && $nch != "\n") {
                    for ($x = ($i + 1);isset($data{$x}) && ltrim($data{$x}, $white_spaces) == ''; $x++) {}
                    if ($data{$x} == $this->delimiter) {
                        $enclosed = false;
                        $i = $x;
                    } else {
                        if ($this->error < 1) {
                            $this->error = 1;
                        }

                        $error_row = count($rows) + 1;
                        $error_col = $col + 1;
                        if (!isset($this->error_info[$error_row . '-' . $error_col])) {
                            $this->error_info[$error_row . '-' . $error_col] = array(
                                'type' => 1,
                                'info' =>
                                'Syntax error found on row ' . (count($rows) + 1) . '. ' .
                                'A single double-quote was found within an enclosed string. ' .
                                'Enclosed double-quotes must be escaped with a second double-quote.',
                                'row' => count($rows) + 1,
                                'field' => $col + 1,
                                'field_name' => (!empty($head[$col])) ? $head[$col] : null,
                            );
                        }

                        $current .= $ch;
                        $enclosed = false;
                    }
                } else {
                    $enclosed = false;
                }

                // end of field/row/csv
            } elseif (($ch == $this->delimiter || $ch == "\n" || $ch == "\r" || $ch === false) && !$enclosed) {
                $key = (!empty($head[$col])) ? $head[$col] : $col;
                $row[$key] = ($was_enclosed) ? $current : trim($current);
                $current = '';
                $was_enclosed = false;
                $col++;

                // end of row
                if ($ch == "\n" || $ch == "\r" || $ch === false) {
                    if ($this->_validate_offset($row_count) && $this->_validate_row_conditions($row, $this->conditions)) {
                        if ($this->heading && empty($head)) {
                            $head = $row;
                        } elseif (empty($this->fields) || (!empty($this->fields) && (($this->heading && $row_count > 0) || !$this->heading))) {
                            if (!empty($this->sort_by) && !empty($row[$this->sort_by])) {
                                if (isset($rows[$row[$this->sort_by]])) {
                                    $rows[$row[$this->sort_by] . '_0'] = &$rows[$row[$this->sort_by]];
                                    unset($rows[$row[$this->sort_by]]);
                                    for ($sn = 1;isset($rows[$row[$this->sort_by] . '_' . $sn]); $sn++) {}
                                    $rows[$row[$this->sort_by] . '_' . $sn] = $row;
                                } else {
                                    $rows[$row[$this->sort_by]] = $row;
                                }

                            } else {
                                $rows[] = $row;
                            }
                        }
                    }

                    $row = array();
                    $col = 0;
                    $row_count++;

                    if ($this->sort_by === null && $this->limit !== null && count($rows) == $this->limit) {
                        $i = $strlen;
                    }

                    if ($ch == "\r" && $nch == "\n") {
                        $i++;
                    }
                }

                // append character to current field
            } else {
                $current .= $ch;
            }
        }

        $this->titles = $head;
        if (!empty($this->sort_by)) {
            $sort_type = SORT_REGULAR;
            if ($this->sort_type == 'numeric') {
                $sort_type = SORT_NUMERIC;
            } elseif ($this->sort_type == 'string') {
                $sort_type = SORT_STRING;
            }

            ($this->sort_reverse) ? krsort($rows, $sort_type) : ksort($rows, $sort_type);

            if ($this->offset !== null || $this->limit !== null) {
                $rows = array_slice($rows, ($this->offset === null ? 0 : $this->offset), $this->limit, true);
            }
        }

        if (!$this->keep_file_data) {
            $this->file_data = null;
        }

        return $rows;
    }

    /**
     * Create CSV data from array
     *
     * @access public
     * @param   data        2D array with data
     * @param   fields      field names
     * @param   append      if true, field names will not be output
     * @param   is_php      if a php die() call should be put on the first
     *                      line of the file, this is later ignored when read.
     * @param   delimiter   field delimiter to use
     *
     * @return  CSV data (text string)
     */
    public function unparse($data = array(), $fields = array(), $append = false, $is_php = false, $delimiter = null) {
        if (!is_array($data) || empty($data)) {
            $data = &$this->data;
        }

        if (!is_array($fields) || empty($fields)) {
            $fields = &$this->titles;
        }

        if ($delimiter === null) {
            $delimiter = $this->delimiter;
        }

        $string = ($is_php) ? "<?php header('Status: 403'); die(' '); ?>" . $this->linefeed : '';
        $entry = array();

        // create heading
        if ($this->heading && !$append && !empty($fields)) {
            foreach ($fields as $key => $value) {
                $entry[] = $this->_enclose_value($value, $delimiter);
            }

            $string .= implode($delimiter, $entry) . $this->linefeed;
            $entry = array();
        }

        // create data
        foreach ($data as $key => $row) {
            foreach ($row as $field => $value) {
                $entry[] = $this->_enclose_value($value, $delimiter);
            }

            $string .= implode($delimiter, $entry) . $this->linefeed;
            $entry = array();
        }

        if ($this->convert_encoding) {
            $string = iconv($this->input_encoding, $this->output_encoding, $string);
        }

        return $string;
    }

    /**
     * Load local file or string
     *
     * @access public
     * @param   input   local CSV file
     *
     * @return  true or false
     */
    public function load_data($input = null) {
        $data = null;
        $file = null;

        if (is_null($input)) {
            $file = $this->file;
        } elseif (file_exists($input)) {
            $file = $input;
        } else {
            $data = $input;
        }

        if (!empty($data) || $data = $this->_rfile($file)) {
            if ($this->file != $file) {
                $this->file = $file;
            }

            if (preg_match('/\.php$/i', $file) && preg_match('/<\?.*?\?>(.*)/ims', $data, $strip)) {
                $data = ltrim($strip[1]);
            }

            if ($this->convert_encoding) {
                $data = iconv($this->input_encoding, $this->output_encoding, $data);
            }

            if (substr($data, -1) != "\n") {
                $data .= "\n";
            }

            $this->file_data = &$data;
            return true;
        }

        return false;
    }

    // ==============================================
    // ----- [ Internal Functions ] -----------------
    // ==============================================

    /**
     * Validate a row against specified conditions
     *
     * @access protected
     * @param   row          array with values from a row
     * @param   conditions   specified conditions that the row must match
     *
     * @return  true of false
     */
    protected function _validate_row_conditions($row = array(), $conditions = null) {
        if (!empty($row)) {
            if (!empty($conditions)) {
                $conditions = (strpos($conditions, ' OR ') !== false) ? explode(' OR ', $conditions) : array($conditions);
                $or = '';
                foreach ($conditions as $key => $value) {
                    if (strpos($value, ' AND ') !== false) {
                        $value = explode(' AND ', $value);
                        $and = '';

                        foreach ($value as $k => $v) {
                            $and .= $this->_validate_row_condition($row, $v);
                        }

                        $or .= (strpos($and, '0') !== false) ? '0' : '1';
                    } else {
                        $or .= $this->_validate_row_condition($row, $value);
                    }
                }

                return (strpos($or, '1') !== false) ? true : false;
            }

            return true;
        }

        return false;
    }

    /**
     * Validate a row against a single condition
     *
     * @access protected
     * @param   row          array with values from a row
     * @param   condition   specified condition that the row must match
     *
     * @return  true of false
     */
    protected function _validate_row_condition($row, $condition) {
        $operators = array(
            '=', 'equals', 'is',
            '!=', 'is not',
            '<', 'is less than',
            '>', 'is greater than',
            '<=', 'is less than or equals',
            '>=', 'is greater than or equals',
            'contains',
            'does not contain',
        );

        $operators_regex = array();

        foreach ($operators as $value) {
            $operators_regex[] = preg_quote($value, '/');
        }

        $operators_regex = implode('|', $operators_regex);

        if (preg_match('/^(.+) (' . $operators_regex . ') (.+)$/i', trim($condition), $capture)) {
            $field = $capture[1];
            $op = $capture[2];
            $value = $capture[3];

            if (preg_match('/^([\'\"]{1})(.*)([\'\"]{1})$/i', $value, $capture)) {
                if ($capture[1] == $capture[3]) {
                    $value = $capture[2];
                    $value = str_replace("\\n", "\n", $value);
                    $value = str_replace("\\r", "\r", $value);
                    $value = str_replace("\\t", "\t", $value);
                    $value = stripslashes($value);
                }
            }

            if (array_key_exists($field, $row)) {
                if (($op == '=' || $op == 'equals' || $op == 'is') && $row[$field] == $value) {
                    return '1';
                } elseif (($op == '!=' || $op == 'is not') && $row[$field] != $value) {
                    return '1';
                } elseif (($op == '<' || $op == 'is less than') && $row[$field] < $value) {
                    return '1';
                } elseif (($op == '>' || $op == 'is greater than') && $row[$field] > $value) {
                    return '1';
                } elseif (($op == '<=' || $op == 'is less than or equals') && $row[$field] <= $value) {
                    return '1';
                } elseif (($op == '>=' || $op == 'is greater than or equals') && $row[$field] >= $value) {
                    return '1';
                } elseif ($op == 'contains' && preg_match('/' . preg_quote($value, '/') . '/i', $row[$field])) {
                    return '1';
                } elseif ($op == 'does not contain' && !preg_match('/' . preg_quote($value, '/') . '/i', $row[$field])) {
                    return '1';
                } else {
                    return '0';
                }
            }
        }

        return '1';
    }

    /**
     * Validates if the row is within the offset or not if sorting is disabled
     *
     * @access protected
     * @param   current_row   the current row number being processed
     *
     * @return  true of false
     */
    protected function _validate_offset($current_row) {
        if ($this->sort_by === null && $this->offset !== null && $current_row < $this->offset) {
            return false;
        }

        return true;
    }

    /**
     * Enclose values if needed
     *  - only used by unparse()
     *
     * @access protected
     * @param  value   string to process
     *
     * @return Processed value
     */
    protected function _enclose_value($value = null, $delimiter = null) {
        if (is_null($delimiter)) {
            $delimiter = $this->delimiter;
        }
        if ($value !== null && $value != '') {
            $delimiter_quoted = preg_quote($delimiter, '/');
            $enclosure_quoted = preg_quote($this->enclosure, '/');
            if (preg_match("/" . $delimiter_quoted . "|" . $enclosure_quoted . "|\n|\r/i", $value) || ($value{0} == ' ' || substr($value, -1) == ' ') || $this->enclose_all) {
                $value = str_replace($this->enclosure, $this->enclosure . $this->enclosure, $value);
                $value = $this->enclosure . $value . $this->enclosure;
            }
        }

        return $value;
    }

    /**
     * Check file data
     *
     * @access protected
     * @param   file   local filename
     *
     * @return  true or false
     */
    protected function _check_data($file = null) {
        if (empty($this->file_data)) {
            if (is_null($file)) {
                $file = $this->file;
            }

            return $this->load_data($file);
        }

        return true;
    }

    /**
     * Check if passed info might be delimiter
     * Only used by find_delimiter
     *
     * @access protected
     * @param  [type] $char      [description]
     * @param  [type] $array     [description]
     * @param  [type] $depth     [description]
     * @param  [type] $preferred [description]
     *
     * @return special string used for delimiter selection, or false
     */
    protected function _check_count($char, $array, $depth, $preferred) {
        if ($depth == count($array)) {
            $first = null;
            $equal = null;
            $almost = false;
            foreach ($array as $key => $value) {
                if ($first == null) {
                    $first = $value;
                } elseif ($value == $first && $equal !== false) {
                    $equal = true;
                } elseif ($value == $first + 1 && $equal !== false) {
                    $equal = true;
                    $almost = true;
                } else {
                    $equal = false;
                }
            }

            if ($equal) {
                $match = ($almost) ? 2 : 1;
                $pref = strpos($preferred, $char);
                $pref = ($pref !== false) ? str_pad($pref, 3, '0', STR_PAD_LEFT) : '999';

                return $pref . $match . '.' . (99999 - str_pad($first, 5, '0', STR_PAD_LEFT));
            } else {
                return false;
            }
        }
    }

    /**
     * Read local file
     *
     * @access protected
     * @param   file   local filename
     *
     * @return  Data from file, or false on failure
     */
    protected function _rfile($file = null) {
        if (is_readable($file)) {
            if (!($fh = fopen($file, 'r'))) {
                return false;
            }

            $data = fread($fh, filesize($file));
            fclose($fh);
            return $data;
        }

        return false;
    }

    /**
     * Write to local file
     *
     * @access protected
     * @param   file     local filename
     * @param   string   data to write to file
     * @param   mode     fopen() mode
     * @param   lock     flock() mode
     *
     * @return  true or false
     */
    protected function _wfile($file, $string = '', $mode = 'wb', $lock = 2) {
        if ($fp = fopen($file, $mode)) {
            flock($fp, $lock);
            $re = fwrite($fp, $string);
            $re2 = fclose($fp);

            if ($re != false && $re2 != false) {
                return true;
            }
        }

        return false;
    }
}
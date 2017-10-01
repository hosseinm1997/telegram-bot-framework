<?php

namespace Robot\Core\BaseClasses;


use Robot\Core\Contracts\IKeyboardTypes;

class BaseKeyboard implements IKeyboardTypes
{
    protected $keys = [];
    protected $row = 0;
    protected $col = 0;
    protected $valid_meta_data;
    /**
     * ReplyKeyboard constructor.
     */
    function __construct()
    {
        if(!empty($keys))
            $this->keys = $keys;

//        $this->keys =
//                array(
//                    array(
//                        array('text'=>'1') , array('text'=>'2') , array('text'=>'5')
//                    ),
//                    array(
//                        array('text'=>'3'),
//                    )
//                );
    }

    protected function insertNewRow($text,$meta = [])
    {
        $this->keys[] = [$this->createKeyContent($text,$meta)];
        return $this->keys;
    }

    protected function createKeyContent($text,$meta = [])
    {
        $text = trim($text,"\n\t\0 ");
        if(empty($text) || is_null($text))
            throw new \Exception('No text set for key');

        $content = ['text' => $text];
        if(!empty($meta) && !is_null($meta))
            foreach ($meta as $key => $value)
            {
                if(array_key_exists($key,$this->valid_meta_data))
                    $content[$key]=$value;
            }
        return $content;
    }

    public function addKey($text,$meta = null)
    {
        $this->keys[$this->row][] = $this->createKeyContent($text,$meta);
        $this->col++;
    }

    public function breakToNewRow()
    {
        $this->row++;
    }

    public function addKeyToRow($row_index,$text,$meta = [])
    {

        if(isset($this->keys[$row_index]))
            $this->keys[$row_index][] = $this->createKeyContent($text,$meta);
        else
            $this->insertNewRow($text);

        return $this->keys;

//        $repkey = json_encode(
//            array(
//                'inline_keyboard' =>
//                    array(
//                        array(array('text'=>'1','callback_data'=>'12',) , array('text'=>'2','callback_data'=>'13',) ),
//                        array(array('text'=>'3','callback_data'=>'14',), array('text'=>'4','callback_data'=>'15',))
//                    )
//            )
//        );
    }

    public function addKeyToColumn($column_index,$text,$meta = [])
    {
        $row_count = count($this->keys);
        if($row_count>0 &&  !@isset($this->keys[$row_count-1][$column_index]))
            $this->keys[count($this->keys-1)][$column_index] = $this->createKeyContent($text,$meta);
        else
            $this->insertNewRow($text);

        return $this->keys;

//        $repkey = json_encode(
//            array(
//                'inline_keyboard' =>
//                    array(
//                        array(array('text'=>'1','callback_data'=>'12',) , array('text'=>'2','callback_data'=>'13',) ),
//                        array(array('text'=>'3','callback_data'=>'14',), array('text'=>'4','callback_data'=>'15',))
//                    )
//            )
//        );
    }

    /**
     * Get or set a cell value
     * To set pass the text value
     * To get leave text value empty
     * @param $row_index
     * @param $column_index
     * @param string $text
     * if no text presented then it returns the cell value if exists
     * @param null $meta
     * @return array|null
     */
    public function cell($row_index, $column_index, $text='', $meta = [])
    {
        if(!array_key_exists($row_index,$this->keys) || !@array_key_exists($column_index,$this->keys[$row_index]))
        {
            if(empty($text))
                return null;
            else
            {
                $this->keys[$row_index][$column_index] = $this->createKeyContent($text,$meta);
            }
        }
        else
        {
            if(empty($text))
                return null;
            else
            {
                $this->keys[$row_index][$column_index] = $this->createKeyContent($text,$meta);
            }
        }

        return $this->keys;
    }

    public function build()
    {
        return json_encode(array('keyboard' => $this->keys));
    }

}
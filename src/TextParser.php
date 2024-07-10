<?php
namespace Vtt;

class TextParser
{

  public $file = '';
  public $path = '';

  private $full_path = '';

  private string $content = '';

  public bool $ready = false;
  public bool $init = false;

  public bool $validation_text = true;
  private array $errors = [];

  public function openFile($file_path, $file_name)
  {
    $this->path = $file_path;
    $this->file = $file_name;

    $this->full_path = "$file_path/$file_name";

    if (file_exists($this->full_path)) {
      $this->content = file_get_contents($this->full_path);
      $this->ready = true;
      return true;
    } else {
      $this->ready = false;
      return false;
    }
  }

  private function parser()
  {


    if ($this->ready) {
      $lines = explode("\n", trim($this->content));

      $line_start_content = false;
      $start_first_line = false;
      $array = [];

      foreach ($lines as $key => $line) {
        $text = trim($line);

        if (strpos($text, '-->') !== false) {
          if($start_first_line == false){
            $start_first_line = true;
          }

          if ($line_start_content == false) {
            $line_start_content = true;
          }

        } else if ($line_start_content == true) {
          if (empty($text)) {
            $line_start_content = false;
          } else {

            $array[] = $text;
          }
        } else if ($line_start_content == false && empty($text) && $start_first_line == true) {
          $this->errors[] = $key;
          $this->validation_text = false;
        }

      }

      $this->init = true;

      return $array;
    }
  }


  public function textValidation()
  {
    if ($this->init == false) {
      $this->parser();
    }

    return ['ok'=>$this->validation_text, 'errors'=>$this->errors];
  }

  public function getTextLines()
  {
    return $this->parser();
  }

}

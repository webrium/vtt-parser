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

  public function parse()
  {


    if ($this->ready) {
      $lines = explode("\n", trim($this->content));

      $line_start_content = false;
      $start_first_line = false;
      $array = [];
      $ob = ['text'=>[], 'time'=>''];

      foreach ($lines as $key => $line) {
        $text = trim($line);

        if (strpos($text, '-->') !== false) {
          if($start_first_line == false){
            $start_first_line = true;
          }

          if ($line_start_content == false) {
            $line_start_content = true;
            $time = $this->extractTimestamps($text);
            $ob['time'] = $time;
          }

        } else if ($line_start_content == true) {
          if (empty($text)) {
            $line_start_content = false;
            $array[]=$ob;
            $ob = ['text'=>[], 'time'=>''];
          } else {
            $ob['text'][] = $text;
          }
        } else if ($line_start_content == false && empty($text) && $start_first_line == true) {
          $this->errors[] = $key;
          $this->validation_text = false;
        }

      }

      if($start_first_line){
        $array[]=$ob;
      }

      $this->init = true;

      return $array;
    }
  }


  private function extractTimestamps($vttTimestampString) {
    // الگوی زمان
    $timePattern = '/(\d{2}:\d{2}:\d{2}.\d{3})/';
  
    // مطابقت با الگو
    if (preg_match_all($timePattern, $vttTimestampString, $matches)) {
      // استخراج زمان ها
      $startTime = $matches[1][0];
      $endTime = $matches[1][1];
  
      // فرمت بندی زمان ها
      $startTimeFormatted = $this->formatTime($startTime);
      $endTimeFormatted = $this->formatTime($endTime);
  
      // ایجاد شیء
      $timestamps = [
        "start" => $startTimeFormatted,
        "end" => $endTimeFormatted
      ];
  
      return $timestamps;
    } else {
      // عدم تطابق الگو
      return null;
    }
  }
  
  // تابع برای فرمت بندی زمان
  private function formatTime($timeString) {
    // جدا کردن اجزای زمان
    $timeComponents = explode(':', $timeString);
  
    // فرمت بندی هر جزء
    $formattedTime = sprintf('%02d:%02d:%02d', $timeComponents[0], $timeComponents[1], $timeComponents[2]);
  
    return $formattedTime;
  }


  public function textValidation()
  {
    if ($this->init == false) {
      $this->parse();
    }

    return ['ok'=>$this->validation_text, 'errors'=>$this->errors];
  }

}

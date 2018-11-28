<?php
    //error_reporting(0);
  
    /**
    
        По какому принципу все работает?
        1) у нас есть одинарные теги и двойные, значит, 
        я должен не учитывать одинарные теги(br, hr и тд), они совершенно не влияют на валидность кода.
        Поможет мне в этом данное регулярное выражение:(<((?!(col|isindex|track|param|hr|br|command|img|bgsound|area|base|basefont|input|link|meta)\b)(.+?))>)
        Я запишу в массив результатов только те теги, которые избавились от  '<' и '>'
        2)получаю массив результатов примерно следующего вида a /a div /div.
        3) Я заметил, что правильно расположенные теги имеют расстояние между собой, равное нечетному числу(учитывается индекс стоящего открывающего тега + расстояние до его закрывающей пары)
        
        $arrayIndexes нужен для сбора индексов тех открывающих тегов, 
        которые нашли свою "пару" и между ними нет какого-то тега без пары.
        
        getResult($array) функция, которая и выполняет проверку на валидность кода HTML.
        Там проверяется размерность  массива arrayIndexes с индексами нашедших свою пару открывающих тегов и
        массива, который был получен путем обработки регуляркой на открывающие теги(без учета пары).
        
        Это формальность, но проверить необходимо на самом начальном уровне. несовпадение размерностей укажет на отсутствие пары для тега.
        И затем просто сравнил их содержимое.
        Если массивы одинаковы, код валиден.
		
		!!Что добавилось в функционал:
		Есть случаи, при которых старая версия скрипта покажет неверный false:
	    Если массив пуст либо состоит из одинарных тегов.
		Значит, надо учесть, что при открывающих тегах = 0, закрывающих тегах = 0 и количестве одинарных тегов >= 0
		код будет валиден.
		
		Проблема данного кода: 
		
		1)Я не следую принципам ООП (Лучше обернуть в классы работу со строками и массивами)
		2)Я следую принципу WET.(DRY рулит, оберну повторяющиеся блоки кода в функцию/функции)
		3)Слишком большая цепочка if (Не очень очевидно, что каждая проверка делает.)
		4)Нужно все же разделить вывод информации и логику.
		
		
		Вывод:
		Рефакторинг кода.
		1) Может, пересмотреть формат подачи промежуточных данных
	    (Использовать массив из числа открывающих, закрывающих и одиночных тегов. это будет проще в плане логики, на мой взгляд)
		2) Don't repeat yourself. 
		3) ООП.
                
    */
  
    function isEquals($value_1, $value_2){
        
        if($value_1==$value_2) 
            return true; 
        else 
            return false;
    }
  
    function validate_html($array) {    
        
        $html = implode($array);
        $arrayIndexes = array();
        preg_match_all (
        '(<((?!col|isindex|track|param|hr|br|command|img|bgsound|area|base|basefont|input|link|meta\b)(.+?))>)', $html, $result);
        
        $tags = $result[1];
        
        for($i = 0; $i < count($tags); $i++){
            
            $tags[$i] = strtolower($tags[$i]);
            //echo $tags[$i]." ";
            
            for($j = $i+1; $j < count($tags); $j++){
                
                if (isEquals ('/'.$tags[$i], $tags[$j]) && ($j - $i) %2 == 1 ){
                    
                    
                    $arrayIndexes[$i] = $i;
                    
                    continue;
                } 
            }
                
        }
        echo "<br>";
        return array_values($arrayIndexes);    
} 

    function checkCouples($array_str) {
        
        preg_match_all('#<(?!col|isindex|track|param|hr|br|command|img|bgsound|area|base|basefont|input|link|meta\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $array_str, $result);
        $opentags = count($result[1]);
        
        preg_match_all('#</+(.+)>#iU', $array_str, $result);
        $closedtags = count($result[1]);
		
        if($closedtags != $opentags){
            
            return false;
        }
        else 
            return true;
    }

    function getResult($array){
		echo '<br><br>';
		$html = implode($array);
		echo "Ваши теги: ".htmlspecialchars($html);
        $indexLen = validate_html($array);
        $html = implode($array);
    
	
		preg_match_all('#<(col|isindex|track|param|hr|br|command|img|bgsound|area|base|basefont|input|link|meta)>#iU', $html, $result);
        $simpletags = count($result[1]);
		echo "Одинарных : ".$simpletags.' при количестве элементов '.count($array)."<br>";
		preg_match_all('#<(?!col|isindex|track|param|hr|br|command|img|bgsound|area|base|basefont|input|link|meta\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU',$html, $result);
        $opentags = count($result[1]);
		
		
		if(checkCouples($html) == true && $opentags == 0 ) {
		
			//;
			if($simpletags < count($array)) {
					echo "Возможно, ошибка в тегах, код не валиден<br>";
					return false;	
			}
			if($simpletags != count($array)) {
				
				echo "Возможно, ошибка в тегах, код не валиден<br>";
		        return false;				
			}
			if ($simpletags == count($array) || ($simpletags == count($array) && count($array) == 0)){
				
				//echo " -- ". $simpletags." -- ".count($array);
				echo "Открывающих и закрывающих тегов нет, одинарных тегов: ".$simpletags.'<br>';
				echo 'html создастся<br>';
				return true;
			}
			
		}
			
		
	
        else if (checkCouples($html) == true) {
            
			$valide = false;
        
            $arrayIndexes = array();
            preg_match_all('#<(?!(col|isindex|track|param|hr|br|command|img|bgsound|area|base|basefont|input|link|meta)\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        
            $tags = $result[1];
			
            if(count($tags)==count ($indexLen) && !empty(count ($indexLen))) {
        
                $correct = 0;
        
                for($i = 0; $i < count($tags); $i ++ ) {
            
                    if($tags[$i] == $array[$indexLen[$i]]){
                
                        $correct ++;
                    }
                    else{
                        
                            $valide = false;
                        }
                }
            
                if($correct == 0) {
            
                    $valide = true;
                }
            }
            else{
        
                $valide = false;
        
            }
            if($valide == true) {
        
                echo "Код html валиден<br>";
            }
            else {
        
                echo "Код html не валиден<br>";
            }
        }
        else {
            
            echo "Код html не валиден<br>";
            return false;
            
        }
    }
    
     //1 тест: код валидный
    $array = array('<div>',
                        '<hr>',
						'<div>',
						'<img>',
                        '</div>',
                    '</div>');
    getResult($array);
    
    //2 тест: нет открывающего тега <a>, не валиден
    $array = array( '<div>','</a>','<div>','</div>','</a>','</div>');
    getResult($array);
    
    //3 тест: Все теги открывающие, не валиден
    $array = array( '<div>','<a>','<div>','<div>','<a>','<div>');
    getResult($array);
    
    //4 тест: Все теги закрывающие, не валиден
    $array = array( '</div>','</a>','</div>','</div>','</a>','</div>');
    getResult($array);
    
	//5 тест: структура правильная, но 1 тег не полностью, не валиден
    $array = array( '<a>','</a','<div>','</div>','<a>','</a>');
    getResult($array);
	
	//6 тест: структура правильная, Тег закрыт, валиден
    $array = array('<a>','</a>','<div>','</div>','<a>','</a>');
    getResult($array);
	
    //7 тест: Все теги имеют правильную структуру по блокам, валиден
    $array = array( '<div>',
                           '<div>',
                                 '<div>',
                                       '<img>',
                                  '</div>',
                                  '<div>',
                                       '<a>',
                                       '</a>',
     					           '</div>',
                           '</div>',
                   '</div>');
    getResult($array);
	
	//8 тест: структурно правильно, Валиден с точки зрения структуры 
    $array = array('<a>','<div>','<a>','<hr>','</a>','</div>','</a>');
    getResult($array);
	
	//9 тест: массив пуст, Пустая страница, выдаст валид
	$array = array();
    getResult($array);
    
	//10 тест: массив содержит незакрытый одиночный тег, должен быть не валид.
	$array = array('<br', 'br>');
    getResult($array); 
    
	//11 тест: валид
	$array = array('<hr>','<hr>','<hr>','<br>','<hr>','<hr>');
    getResult($array);
?>

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
		Если массивы одинаковы, код валиден
				
	*/
  
  
	function getId ($array, $value){
		
		for ($i = 0; $i < count($array); $i++){
			
			if($array[$i]==$value) return $i;
			
			else return null;
		}
	}
  
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
		'(<((?!(col|isindex|track|param|hr|br|command|img|bgsound|area|base|basefont|input|link|meta)\b)(.+?))>)', $html, $result);
		
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

	function getResult($array){
	
		$indexLen = validate_html($array);
		$valide = false;
	
	
		$html = implode($array);
		$arrayIndexes = array();
		preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
		
		$tags = $result[1];
	//var_dump($tags);
	
	
		if(count($tags)==count ($indexLen)) {
		
			$correct = 0;
		//
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
		
			echo "Код html валиден";
		}
		else {
		
			echo "Код html не валиден";
		}
	}

	$tagsArray = array('<div>','<hr>','<hr>','<a>','</a>','<p>','</p>', '</div>'); // правильный код
	getResult($tagsArray);
	

	$incorrect = array('<div>','<a>','<div>','</div>','</a>','</div>'); //неправильный код
	getResult($incorrect);
?>

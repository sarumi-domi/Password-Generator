<?php
	/**
	 * Diese Klasse  stellt  Funktionen zum  Generieren von  Passwoertern
	 * anhand frei definierbarer Muster in Form von Zeichengruppen bereit.
	 * 
	 * @copyright	2015 Marius Gerum
	 * @author		Marius Gerum <me[at]marius-gerum[dot]de>
	 * @license		http://creativecommons.org/licenses/by/3.0/legalcode
	 */
	
	class PasswordGenerator{
		
		private static $tags = array('\{', '\}');
		private static $rules = array(array('/\/\*.*?\*\//i', ''));
		public static function get($pattern){
			/**
			 * Festgelegte Regeln anwenden:
			 */
			foreach(self::$rules as $set){
				$pattern = @preg_replace($set[0], $set[1], $pattern);
			}
			
			// Schauen dass leere Gruppen entfernt werden (beisp. "{}" oder "{,2}")
			$pattern = @preg_replace('/' . self::$tags[0] . '(\,[0-9]+)?' . self::$tags[1] . '/i', '', $pattern);
			
			/**
			 * Zeichengruppen rausfiltern und alle Funde in ner Schleife durchgehen
			 */
			$Matches = array();
			@preg_match_all('/' . self::$tags[0] . '.*?' . self::$tags[1] . '/i', $pattern, $Matches);
			$Matches = $Matches[0];
			$grpV = array(); // <- Erklaerung weiter unten
			foreach($Matches as $value){
				/**
				 * $repeat legt fest, ob mehrere Zeichen in einer Zeichengruppe generiert werden
				 * (z.B. {abc,4}) oder nur ein Zeichen (z.B. {abc})
				 */
				$repeat = 1;
				if(@preg_match('/\,[0-9]+' . self::$tags[1] . '$/', $value)){
					@preg_match('/\,[0-9]+' . self::$tags[1] . '/', $value, $l);
					$l = @preg_replace('/[^0-9]/', '', $l[0]);
					if(is_numeric($l)){
						$repeat = $l;
					}
					$value = @preg_replace('/\,[0-9]+' . self::$tags[1] . '/', '}', $value);
				}
				$gen_chars = "";	//generierte Zeichen - erstmal leer
				for($i = 0; $i < $repeat; $i++){
					/**
					 * Hier werden die Zeichen die sich in einer Gruppe befinden generiert
					 */
					$gen_chars .= substr($value, rand(1, strlen($value)-2), 1);
				}
				$grpV[] = $gen_chars;
				$pattern = @preg_replace('/' . self::$tags[0] . '.*?' . self::$tags[1] . '/', $gen_chars, $pattern, 1);
			}
			
			/**
			 * Erstmal wurde jede aufgeloeste Zeichengruppe in das Array aufgenommen. Jetzt werden 
			 * alle Funde in nem Array durchlaufen, sodass Rueckbezuege im Pattern ebenfalls
			 * korrekt aufgeloest werden koennen. 
			 */
			foreach($grpV as $key => $value){
				$pattern = @preg_replace('/\:v\=' . ++$key . '\:/', $value, $pattern);
			}
			
			/**
			 * So, hier der wesentliche Teil, mit dem wir dann auch was anfangen koennen ;)
			 */
			return $pattern;
		}
		
		
		/**
		 * Methode setup() um ggf. an dieser Stelle bereits Voreinstellungen zu treffen
		 */
		public static function setup(){
			/* [...] */
		}
		
		/**
		 * Methode mit der festgelegt werden kann, wie Zeichengruppen gefunden werden
		 * Standard: Alles zwischen { und } wird als Zeichengruppe interpretiert
		 *
		 * Anwendungsbeispiel:
		 * PG::setTags('\[', '\]');
		 * Sorgt dafuer, dass Zeichengruppen anstatt so:
		 * {abc}
		 * nun so erkannt werden:
		 * [abc]
		 *
		 * Hinweis: entsprechende Zeichen ggf. escapen, da diese als RegEx-Pattern
		 * interpretiert werden.
		 */
		public static function setTags($open, $close){
			self::$tags = array($open, $close);
		}
		
		/**
		 * Regeln festlegen (Suchen -> Ersetzen)
		 * 
		 * Hier kannst du Regeln definieren. Diese legst du zu Beginn deines Skripts fest
		 *
		 * Beispiel:
		 * PG::addRule(array('/\:a-z\:/', 'abcdefghijklmnopqrstuvwxyz'));
		 * Wenn im Muster jetzt der Text :a-z: gefunden wird, wird er durch alle Buchstaben des Alphabets ersetzt.
		 * Spart in erster Linie Schreibarbeit.
		 * In der Praxis:
		 * {:a-z:,2}{123}{:a-z:}
		 * ist deutlich schneller zu schreiben als
		 * {abcdefghijklmnopqrstuvwxyz,2}{123}{abcdefghijklmnopqrstuvwxyz}
		 */
		public static function clearRules(){
			self::$rules = array();
		}
		public static function addRule($rule){
			self::$rules[] = $rule;
		}
		
	}
?>
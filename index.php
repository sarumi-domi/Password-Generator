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
	
	/**
	 * Klassen-Alias - damit's weniger zum tippen ist
	 */
	use PasswordGenerator as PG;
	
	/**
	 * Anwendungsbeispiel:
	 * $pass = PG::get('{abcdef0123456789,16}');
	 *
	 * Im einfachsten Fall war's das jetzt auch schon. Jetzt nur noch das Passwort z.B. mit "echo $pass;" ausgeben, fertig.
	 *
	 * Der Code ab hier bis runter dient jetzt nur noch dazu, das Ganze testen zu koennen.
	 */
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * Einige Ersetzungsregeln damit ich z.B. Klein-/Grossbuchstaben und Zahlen nicht immer ausschreiben muss
	 * sondern einfach :lns: als Abkuerzung schreiben kann.
	 */
	
	// Zahlen, Klein- und Grossbuchstaben
	PG::addRule(array('/\:lns\:/i', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'));
	// Selbstlaute (letters, numbers, specialchars)
	PG::addRule(array('/\:sl\:/i', 'aeiou'));
	// Zahlen (numbers)
	PG::addRule(array('/\:num\:/i', '0123456789'));
	// Kleinbuchstaben (lowercase-letters)
	PG::addRule(array('/\:lcl\:/i', 'abcdefghijklmnopqrstuvwxyz'));
	// Grossbuchstaben (uppercase-letters)
	PG::addRule(array('/\:ucl\:/i', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'));
	// Klein- und Grossbuchstaben (upper-/lowercase letters)
	PG::addRule(array('/\:ull\:/i', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'));
	// Und was dir sonst noch einfaellt.....
	PG::addRule(array('/\:hurr\:/i', 'durr'));
	
	
	/**
	 * $Values enthaelt die Werte. Wenn via POST was uebergeben wurde, entsprechend die
	 * aktuellen Werte verwenden anstatt die vordefinierten.
	 */
	$Values = array();
	
	/* Standard-Passwortmuster - damit man schon Ergebnisse sieht auch wenn man noch nix submitted hat */
	$Values['pattern'] = "{abcdef0123456789,16}";
	if(isset($_POST['pattern'])){
		$Values['pattern'] = $_POST['pattern'];
	}
	$Values['pattern'] = @preg_replace('/\"|\'/', '', $Values['pattern']); // Raus mit den einfachen und doppelten Anfuehrungszeichen
	
	/* Anzahl zu generierender Passwoerter */
	$Values['amount'] = "4";
	if(isset($_POST['amount']) && is_numeric($_POST['amount'])){
		$Values['amount'] = $_POST['amount'];
	}
	
	/**
	 * Pattern wird uebergeben an PG::get(...), sooft wie die eingestellte
	 * Anzahl zu generierender Passwoerter
	 */
	$pw = "";
	for($i = 0; $i < $Values['amount']; $i++){
		/* Bei Bedarf hier true als zweites Argument uebergeben */
		$pw .= PG::get($Values['pattern']) . "\n";
	}
	
	/**
	 * Hier dann letzendlich alle generierten Passwoerter als String, wie
	 * sie dann unten in der textarea ausgegeben werden
	 */
	$Values['pass'] = $pw;
	
	
	
	
	
	/**
	 * 
	 * Ausgabe / Website ab hier
	 * 
	 * 
	 */
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Passwort-Generator</title>
		
		<!-- CSS-Stylesheets -->
		<link rel="stylesheet" type="text/css" href="./style.css">
		
		<!-- JavsScript -->
		<script src="//code.jquery.com/jquery-1.11.2.min.js"></script><!-- ### Ja die Performance-Einbussen durch jQuery nehm' ich gern in Kauf ### -->
	</head>
	
	<body>
		<div id="main">
			<form method="POST">
				<table>
					<tr>
						<td>Muster</td>
						<td>
							
							<!-- ### Entweder vordefinierte Passwort-Muster auswaehlen oder selbst eingeben (Textfeld verwenden) ### -->
							<select name="template">
								<option value="">Kein Template, ich gebe das Muster selbst ein:</option>
								<option value="" DISABLED></option>
								<option value="" DISABLED>Hier einige vordefinierte Muster:</option>
								<?php
									/**
									 * So, hier auch mal ein paar vordefinierte Pattern zur Veranschaulichung und zum
									 * rumprobieren.
									 */
									$templates = array();
									$templates[] = array("{abcdef}", "Zum Herumexperimentieren 1");
									$templates[] = array("{abcdef01234}", "Zum Herumexperimentieren 2");
									$templates[] = array("{abcdef,3}", "Zum Herumexperimentieren 3");
									$templates[] = array("{abcdef0123456789,5}", "Zum Herumexperimentieren 4");
									$templates[] = array("{AaBbCc,4}", "Zum Herumexperimentieren 5");
									$templates[] = array("{abx}{123}{-._}", "Zum Herumexperimentieren 6");
									$templates[] = array("{abx}{123,2}{-._}", "Zum Herumexperimentieren 7");
									
									$templates[] = array("{abcdef0123456789,32}", "MD5-Hash");
									$templates[] = array("{abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!%$#?_.,32}", "Zufallszeichen, L&auml;nge 32");
									$templates[] = array("{0123456789,8}", "Zahlenfolge, L&auml;nge 8");
									$templates[] = array("{10,8}", "Bin&auml;rcode, 8 Zeichen");
									$templates[] = array("{aeiou,14}", "Selbstlaute, 14 Zeichen");
									/**
									 * Tipp: aussprechbare Passwoerter lassen sich einfach bewerkstelligen,
									 * man muss einfach nur abwechselnd einen harten Buchstaben und einen Selbstlaut 
									 * verknuepfen. Das Wort ist zwangslaeufig gut aussprechbar. 
									 */
									$templates[] = array('{WGTZKLBPSR}{aeiou}{tklwbgzspr}{aeiou}{.-_}{0123456789,2}{!%$?}', "Aussprechbares Passwort (1)");
									$templates[] = array('{WGTZKLBPSR}{aeiou}{tklwbgzspr}{aeiou}{tklwgzbspr}{aeiou}{.-_}{0123456789,2}{!%$?}', "Aussprechbares Passwort (2)");
									$templates[] = array('{WGTZKLBPSR}{aeiou}{tklwbgzspr}{aeiou}{tklwgzbspr}{aeiou}{tklwgzbspr}{aeiou}{.-_}{0123456789,2}{!%$?}', "Aussprechbares Passwort (3)");
									$templates[] = array('{WGTZKLBPSR}{aeiou}{tklwbgzspr}{aeiou}{tklwgzbspr}{aeiou}', "Aussprechbares Passwort (4)");
									$templates[] = array('{TKMSH}{aeiou,2}{gdk}{aeiou}{zsgt}{aeiou}', "Aussprechbares Passwort (5)");
									$templates[] = array('pre_{abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789%?!$,6}', "Passwort mit Pr&auml;fix");
									/**
									 * !! Achtung !!
									 * Abkuerzungen funktionieren nur, wenn Ersetzungs-Regeln festgelegt wurden (PG::setRules(...) oder PG::addRule(...))
									 */
									$templates[] = array("{:lcl::ucl::num:,50}", "Kleinbuchstaben, Grossbuchstaben, Zahlen - 50 Zeichen - Veranschaulichung der Ersetzungsregeln");
									$templates[] = array("{abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789,64}", "WPA2-Schl&uuml;ssel");
									$templates[] = array("{:lns:,64}", "WPA2-Schl&uuml;ssel (Abk&uuml;rzung, aber erzeugt das gleiche wie der Eintrag davor)");
									$templates[] = array("{:ull:,30}", "Gross- und Kleinbuchstaben, 30 Zeichen");
									$templates[] = array("{:8}-{3DO()/}", "Smileys");
									$templates[] = array("{TSRLPKFG}{abcdefghijklmnopqrstuvwxyz}:v=2:{0123456789,2}", "Zeichenwiederholungen");
									$templates[] = array("[  ] {abcdef0123456789,32}", "Passwort-Checkliste (hierf&uuml;r &quot;Anzahl&quot; auf mehr als 1 einstellen)");
									$templates[] = array("[  ] {abcdef0123456789,32}   Bemerkung: _______________", "Passwort-Checkliste 2 (hierf&uuml;r &quot;Anzahl&quot; auf mehr als 1 einstellen)");
									$templates[] = array("Passwort fuer Benutzer _______________ : {:ucl:}{abcdef0123456789,8}_{:num:,2}{!%$?}", "Passwort-Checkliste 3 (hierf&uuml;r &quot;Anzahl&quot; auf mehr als 1 einstellen)");
									
									/* vordefinierte Muster durchlaufen ... */
									foreach($templates as $row){
										echo "<option value='" . $row[0] . "'";
										/* Wenn Passwort-Muster dem aktuellen Eintrag entspricht, selected-Attribut festlegen */
										if($Values['pattern'] == $row[0]){
											echo " selected";
										}
										echo ">" . $row[1] . "</option>";
									}
								?>
							</select><br>
							<input type="text" name="pattern" value="<?php echo @htmlspecialchars($Values['pattern'], ENT_QUOTES); ?>" autofocus>
						</td>
					</tr>
					
					<!-- ### Festlegen, wie viele Passwoerter generiert werden sollen ### -->
					<tr>
						<td>Anzahl</td>
						<td><input type="text" name="amount" value="<?php echo $Values['amount']; ?>"></td>
					</tr>
					
					<!-- ### Die generierten Passwoerter ### -->
					<tr>
						<td>Passwort</td>
						<td><textarea READONLY><?php echo @htmlspecialchars($Values['pass'], ENT_QUOTES); ?></textarea></td>
					</tr>
				</table>
				<br>
				<p><input type="submit" name="submit" value="Generieren"></p>
			</form>
		</div>
		<script type="text/javascript">
			/**
			 * Wenn ein vordefiniertes Muster ausgewaehlt wird, dies in
			 * die Textbox einfuegen.
			 */
			$('select[name=template]').on('change', function (e) {
				var so = $("option:selected", this);
				var sv = this.value;
				$('input[name=pattern]').attr('value', sv);
			});

		</script>
	</body>
</html>
<!-- ### Ende ### -->
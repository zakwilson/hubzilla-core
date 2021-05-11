
Tworzenie wtyczek (dodatków) do $Projectname
============================================

Przypuszczalnie chcesz, aby $Projectname zrobił coś, czego jeszcze nie robi. Jest wiele sposobów. Ale nauczmy się, jak napisać wtyczkę lub dodatek.

W katalogu $Projectname prawdopodobnie zobaczysz podkatalog o nazwie "addon". Jeśli jeszcze go nie masz, utwórz go.

	mkdir addon

Następnie wymyśl nazwę swojego dodatku. Prawdopodobnie masz już jakieś pojęcie o tym, co chcesz, aby robił. Na potrzeby naszego przykładu utworzymy wtyczkę o nazwie "randplace", która zapewni nieco losową lokalizację każdego z Twoich wpisów. Nazwa wtyczki będzie służyć do znajdowania funkcji, które trzeba użyć i jest częścią nazwy każdej z tychfunkcji, więc dla bezpieczeństwa używaj tylko prostych znaków tekstowych.

Po wybraniu nazwy wtyczki, utwórz katalog wewnątz 'addon', aby przechowywać tu pliki wtyczki.

	mkdir addon/randplace


Teraz utwórz plik wtyczki. Musi mieć taką samą nazwę i jest to skrypt PHP, więc za pomocą swojego ulubionego edytora utwórz plik

	addon/randplace/randplace.php

Pierwszą linią tego pliku musi być fraza

	<?php

Następnie utworzymy blok komentarza, aby opisać wtyczkę. Jest do tego specjalny format. Używamy / * ... * / w stylu komentarza i niektórych oznaczonych linii składających się z

	/**
	 *
	 * Name: Random Place (here you can use better descriptions than you could in the filename)
	 * Description: Sample $Projectname plugin, Sets a random place when posting.
	 * Version: 1.0
	 * Author: Mike Macgirvin <mike@zothub.com>
	 *
	 */

Te atrybuty będą widoczne dla administratora strony, gdy instaluje lub zarządza wtyczkami z panelu administracyjnego. Może być więcej autorów. W takim przypadku, po prostu dodaj kolejną linię zaczynającą się od "Autor:".

Typowa wtyczka będzie miała co najmniej następujące funkcje:

* pluginname_load()
* pluginname_unload()

W naszym przypadku nazwiemy je `randplace_load()` i `randplace_unload()`, bo taka jest nazwa naszej wtyczki. Te funkcje są wywoływane za każdym razem, gdy chcemy zainicjować wtyczkę lub usunąć ją z bieżącej strony internetowej. Również jeśli wtyczka wymaga rzeczy takich jak zmiana schematu bazy danych przed uruchomieniem jej po raz pierwszy, trzeba będzie umieścić poniższe funkcje:

* pluginname_install()
* pluginname_uninstall()

Następnie omówimy **zaczepy**. Zaczepy (*ang. hooks*) to miejsca w kodzie $Projectname, do których można "podczepić" kod wtyczki, aby go tam wykonywać. Zwykle wykorzystuje się funkcję `pluginname_load()` do zarejestrowania "funkcji obsługi" dla potrzebnych zaczepów. Następnie, gdy zostanie wyzwolony którykolwiek z tych zaczepów, zostanie wywołany podpięty tam kod.

Zarejestrujmy więc program obsługi zaczepów za pomocą funkcji `register_hook()`. Potrzebne są trzy argumenty. Pierwszy to nazwa zaczepu, który chcemy obsłużyć, drugi to nazwa pliku, który ma znaleźć naszą funkcję obsługi (ścieżka względem katalogu instalacyjnego $Projectname), a trzeci to nazwa funkcji. Stwórzmy więc teraz naszą funkcję `randplace_load()`.

```
function randplace_load() {
	register_hook('post_local', 'addon/randplace/randplace.php', 'randplace_post_hook');

	register_hook('feature_settings', 'addon/randplace/randplace.php', 'randplace_settings');
	register_hook('feature_settings_post', 'addon/randplace/randplace.php', 'randplace_settings_post');
}
```

Tak więc przechwycimy trzy zdarzenia: `post_local`, które jest wywoływane, gdy w systemie lokalnym pojawia się wpis, `feature_settings`, aby ustawić pewne preferencje dla naszej wtyczki, oraz `feature_settings_post`, aby przechowywać te ustawienia.

Następnie utworzymy funkcję unload. Jest to łatwe, ponieważ wystarczy wyrejestrować nasze zaczepy. Wymaga to dokładnie tych samych argumentów. 

```
function randplace_unload() {
	unregister_hook('post_local', 'addon/randplace/randplace.php', 'randplace_post_hook');
	unregister_hook('feature_settings', 'addon/randplace/randplace.php', 'randplace_settings');
	unregister_hook('feature_settings_post', 'addon/randplace/randplace.php', 'randplace_settings_post');
}
```
Zaczepy są wywoływane z dwoma argumentami. Pierwszą to zawsze $a, który jest naszą globalną strukturą aplikacji i zawiera ogromną ilość informacji o stanie przetwarzanego żądania HTTP; a także o tym kim jest przeglądający i jaki jest nasz stan logowania oraz aktualną zawartość strony internetowej, którą prawdopodobnie tworzymy.

Drugi argument jest specyficzny dla zaczepu, który chce się wywołać. Zawiera informacje istotne dla tego konkretnego miejsca w programie i często pozwala na jego przegląd a nawet zmianę. Aby to zmienić, trzeba dodać zanak "&" do nazwy zmiennej, aby była przekazywana do funkcji przez odniesienie. W przeciwnym razie utworzona zostanie kopia i wszelkie wprowadzone zmiany zostaną utracone przy ponownym przetworzeniu zaczepu. Zwykle (ale nie zawsze) drugim argumentem jest nazwana tablica struktur danych.

Dodajmy więc poniższy kod, aby zaimplementować nasz moduł obsługi zaczepu:

```
function randplace_post_hook($a, &$item) {

/**
*
* W systemie lokalnym został wpisany jakiś element.
* Będziemy wyszukiwać określonych elementów:
*  - Wpis napisany przez właściciela profilu
*  - Właściciel profilu musi zezwolić na naszą wtyczkę
*
*/

logger('randplace invoked');

if(! local_channel())   /* nie zero jeśli zalogowany jest użytkownik systemu */
	return;

if(local_channel() != $item['uid'])    /* Czy ta osoba jest właścicielem wpisu? */
	return;

if(($item['parent']) || (! is_item_normal($item))) {
	/* Jeśli element ma rodzica lub nie jest „normalny”, jest to komentarz lub coś innego, a nie wpis. */
	return;
}

/* Pobranie osobistych ustawień konfiguracyjnych */

$active = get_pconfig(local_channel(), 'randplace', 'enable');

if(! $active)
	return;
	/**
	*
	* OK, wolno nam robić swoje.
	* Oto, co zamierzamy zrobić:
	* załadowanie listy nazw stref czasowych i użycie jej do wygenerowania listy miast na świecie.
	* Następnie wybierzemy losowo jedno z nich i umieścimy je w polu "location" wpisu.
	*
	*/

	$cities = array();
    $zones = timezone_identifiers_list();
	foreach($zones as $zone) {
    	if((strpos($zone,'/')) && (! stristr($zone,'US/')) && (! stristr($zone,'Etc/')))
       		$cities[] = str_replace('_', ' ',substr($zone,strpos($zone,'/') + 1));
	    }

		if(! count($cities))
			return;
		$city = array_rand($cities,1);
		$item['location'] = $cities[$city];
		
		return;
}
```

Teraz dodajmy nasze funkcje do ustawień preferencji tworzenia i przechowywania.

```
/**
*
* Wywołanie zwrotne z funkcji ustawień wpisu.
* $post zawiera globalną tablicę $_POST.
* Upewnimy się, że mamy ważne konto użytkownika 
* i że kliknięto tylko nasz własny przycisk submit
* a jeśli tak, to ustawiamy ustawienia konfiguracyjne dla tego użytkownika.
*
*/

function randplace_settings_post($a,$post) {
	if(! local_channel())
		return;
	if($_POST['randplace-submit'])
		set_pconfig(local_channel(),'randplace','enable',intval($_POST['randplace']));
}

/**
*
* Wywoływanie z formularza ustawień funkcjonalności.
* Drugim argumentem jest w tym przypadku łańcuch, region treści HTML strony.
* Dodanie własnych informacje o ustawieniach do tego łańcucha.
*
* Aby zapewnić jednolitość stron ustawień, stosujemy następującą konwencję
*     <div class="settings-block">
*       <h3>title</h3>
*       .... settings html - będzie wiele elementów pływających ...
*       <div class="clear"></div> <!-- klasa ogólna, która czyści wszystkie elementy pływające -->
*       <input type="submit" name="pluginnname-submit" class="settings-submit" ..... />
*     </div>
*/

function randplace_settings(&$a,&$s) {

	if(! local_channel())
		return;

	/* Dodanie naszego arkusza stylów do strony, aby ładnie wyglądała strona ustawień */

	head_add_css('/addon/randplace/randplace.css');
	
	/* Pobranie aktualnego stan naszej zmiennej konfiguracyjnej */

	$enabled = get_pconfig(local_channel(),'randplace','enable');

	$checked = (($enabled) ? ' checked="checked" ' : '');

	/* Dodaj trochę HTML do istniejącego formularza */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('Randplace Settings') . '</h3>';
	$s .= '<div id="randplace-enable-wrapper">';
	$s .= '<label id="randplace-enable-label" for="randplace-checkbox">' . t('Enable Randplace Plugin') . '</label>';
	$s .= '<input id="randplace-checkbox" type="checkbox" name="randplace" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* dodanie przycisku przesyłania */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="randplace-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}
```

***Zaawansowane wtyczki***

Czasami zachodzi potrzeba zapewnienia jakichś nowych funkcji, których w ogóle nie ma lub których nie można zapewnić za pomocą zaczepów. W tym przypadku wtyczka może również działać jako "moduł". Moduł w naszym przypadku odnosi się do ustrukturyzowanej procedury obsługi strony internetowej, która odpowiada na podany adres URL. Wtedy wszystko, co uzyskuje dostęp do tego adresu URL, będzie obsługiwane w całości przez wtyczkę.

Kluczem do tego jest stworzenie prostej funkcji o nazwie `pluginname_module()`, która nic nie robi.

```
function randplace_module() { return; }
```

Gdy ta funkcja już istnieje, adres URL https://twoja_witryna/randplace będzie uzyskiwał dostęp do wtyczki jako modułu. Następnie możesz zdefiniować funkcje, które są wywoływane w różnych miejscach w celu zbudowania strony internetowej, tak jak moduły w katalogu mod/. Oto typowe funkcje i kolejność ich wywoływania

```
modulename_init($a)    // (e.g. randplace_init($a);) wywoływana jako pierwsza.
						  // Gdy chce się emitować json lub xml, powinno się to
						  // zrobić tutaj, a następnie wywołać killme(), co pozwoli
						  // uniknąć domyślnej akcji budowania strony internetowej.
modulename_aside($a)   // często uzywana di tworzenia zawartości paska bocznego
modulename_post($a)    // wywoływana za każdym razem, gdy strona jest otwierana
                       // za pomocą metody "post"
modulename_content($a) // wywoływana w celu wygenerowania zawartości strony centralnej.
                       // Ta funkcja powinna zwracać łańcuch znaków składający się
                       // z centralnej yteści strony.
```

Funkcje modułu mają dostęp do ścieżki URL tak, jakby były samodzielnymi programami w systemie operacyjnym Unix. Dla przykładu, w naszego module stwórzmy coś co działa pod adresem:
	
	https://yoursite/randplace/something/somewhere/whatever

Bedzie to listę argc i argv do wykorzystania przez funkcje tego modułu

```
$x = argc(); $x will be 4, the number of path arguments after the sitename

for($x = 0; $x < argc(); $x ++)
	echo $x . ' ' . argv($x);

	0 randplace
	1 something
	2 somewhere
	3 whatever
```

***Przenoszenie wtyczek Friendica***

$Projectname wykorzystuje podobną architekturę wtyczek do projektu Friendica. Mechanizmy uwierzytelniania, tożsamości i uprawnień są zupełnie inne. Wiele wtyczek Friendica można stosunkowo łatwo przenosić, zmieniając nazwy kilku funkcji i następnie zapewniając przestrzeganie modelu uprawnień. Funkcje, których nazwy wymagają zmiany, to:

* Funkcja Friendica `pluginname_install()` to `pluginname_load()`

* Funkcja Friendica `pluginname_uninstall()` to `pluginname_unload()`

$Projectname ma funkcje `_install` i `_uninstall`, ale są one używane w inny sposób.

* Funkcja zaczepu w Friendica `plugin_settings` ma nazwę `feature_settings`

* Funkcja zaczepu Friendica `plugin_settings_post` ma nazwę `feature_settings_post`

Zmiana tych ustawień często pozwoli na działanie wtyczki, ale proszę dokładnie sprawdzić wszystkie uprawnienia i kod identyfikacyjny, ponieważ koncepcje, które za tym stoją, są zupełnie inne w $Projectname. Wiele nazw danych strukturalnych (zwłaszcza kolumny schematu bazy danych) jest również zupełnie inna. 

#include doc/macros/main_footer.bb;

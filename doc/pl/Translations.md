Tłumaczenie $Projectname
========================

Procedura tłumaczenia
---------------------

Ciągi używane w interfejsie użytkownika $Projectname są tłumaczone
w [Transifex][1], a następnie przeniesione do repozytorium Git na
github. Jeśli chcesz pomóc w tłumaczeniu dla dowolnego języka, czy
to poprawianie warunków, czy tłumaczenie $Projectname na plik aktualnie
nieobsługiwany język, zarejestruj konto na transifex.com i skontaktuj
się z tamtejszym zespołem tłumaczy Redmatrix.

Tłumaczenie $Projectname jest proste. Po prostu użyj narzędzia online
w transifex. Jeśli nie chcesz mieć do czynienia z git & co. w porządku,
regularnie sprawdzamy status tłumaczeń i importujemy je do drzewa
źródłowego na github, aby inni mogli z nich korzystać.

Nie uwzględniamy każdego tłumaczenia z transifex w drzewie źródłowym,
aby uniknąć rozproszonego i zakłóconego ogólnego doświadczenia. Jako
niewykształcone przypuszczenie mamy dolną granicę 50% przetłumaczonych
ciągów, zanim włączymy język. Limit ten jest oceniany tylko na podstawie
ilości przetłumaczonych ciągów przy założeniu, że najbardziej widoczne
ciągi dla interfejsu użytkownika zostaną przetłumaczone jako pierwsze
przez zespół tłumaczący. Jeśli uważasz, że Twoje tłumaczenie będzie
przydatne przed tym limitem, skontaktuj się z nami, a prawdopodobnie
uwzględnimy pracę Twoich zespołów w drzewie źródłowym.

Jeśli chcesz samodzielnie przenieść swoją pracę do drzewa źródłowego,
zrób to i skontaktuj się z nami i zadaj pytanie, które się pojawi.
Proces jest prosty, a oprogramowanie $Projectname jest dostarczane ze wszystkimi
niezbędnymi narzędziami.

Lokalizacją przetłumaczonych plików jest w drzewie źródłowym katalog
`/view/LNG-CODE/`, ggdzie LNG-CODE jest używanym kodem języka, np.
`de` dla niemieckiego lub `fr` dla francuskiego.
W przypadku szablonów wiadomości e-mail (pliki `*.tpl`) po prostu umieść
je w katalogu i gotowe. Przetłumaczone łańcuchy pochodzą z pliku
"hmessages.po" z transifex, który należy przetłumaczyć na plik PHP
używany przez $Projectname. Aby to zrobić, umieść plik w wymienionym
wyżej katalogu i użyj narzędzia `po2php` z katalogu `util` w instalacji
$Projectname.

Zakładając, że chcesz przetłumaczyć niemiecką wersję umieszczoną pliku
`view/de/hmessages.po`, wykonaj następujące czynności.

1. Przejdź w wierszu polecenia do katalogu głównego instalacji $Projectname

2. Wykonaj skrypt `po2php`, który jest umieszczono tłumaczenia dla pliku `hstrings.php`, który jest używany w $Projectname.

       $> php util/po2php.php view/de/hmessages.po

   Dane wyjściowe skryptu zostaną umieszczone w `view/de/hstrings.php, gdzie
   froemdoca oczekuje tego pliku, więc możesz natychmiast przetestować swoje
   tłumaczenie.
                                  
3. Odwiedź swoją stronę $Projectname, aby sprawdzić, czy nadal działa w języku, który właśnie przetłumaczyłeś. Jeśli nie, spróbuj znaleźć błąd, najprawdopodobniej PHP da ci wskazówkę w opisie błędu w `log/warnings.about`.

   W celu debugowania możesz również spróbować "uruchomić" plik za pomocą PHP. Nie powinno to dawać żadnych wyników, jeśli plik jest w porządku, ale może dać wskazówkę dotyczącą wyszukiwania błędu.

       $> php view/de/hstrings.php

4. Zatwierdź te dwa pliki z sensownym komunikatem o zatwierdzeniu do repozytorium git, wypchnij je do rozwidlenia repozytorium $Projectname na github i wydaj żądanie ściągnięcia dla tego zatwierdzenia.

Narzędzia
---------

Oprócz skryptu po2php, jest jeszcze w katalogu "util" w drzewie źródłowym $Projectname
kilka narzędzi do tłumaczenia. Jeśli tylko chcesz przetłumacz $Projectname na inny
język, którego  nie potrzebujesz najbardziej, ale da Ci to wyobrażenie o procesie
tłumaczenia $Projectname.

Więcej informacji można znaleźć w pliku utils/README.

Znane problemy
--------------

* $Projectname używa ustawień języka przeglądarki odwiedzających, aby określić
  język interfejsu użytkownika. W większości przypadków to działa, ale są pewne
  znane dziwactwa.
* wczesne tłumaczenia są oparte na przekładach Friendica, jeśli znajdziesz jakieś
  błędy, daj nam znać lub popraw je w Transifex.

Linki
------
[1]:   http://www.transifex.com/projects/p/hubzilla/


#include doc/pl/macros/main_footer.bb;

Komunikat serwera WWW pod adresem {{$sitename}};
   
Rutynowa kontrola wskazuje, że certyfikat SSL dla tej witryny jest
nieważny. Twój serwis internetowy nie może w pełni uczestniczyć w Hubzilli
dopóki ten problem nie zostanie rozwiązany. Sprawdź swój certyfikat i swojego
dostawcę certyfikatu lub usługodawcę, aby upewnić się, że jest on "akceptowany
przez przeglądarkę” i prawidłowo zainstalowany. Certyfikaty z podpisem własnym
NIE SĄ OBSŁUGIWANE i NIE SĄ DOZWOLONE w Hubzilli.

Sprawdzenie odbywa się poprzez pobranie adresu URL z Twojej witryny z włączonym
ścisłym sprawdzaniem SSL, a jeśli to się nie powiedzie, ponowne sprawdzenie z SSL
z kontrolą wyłączoną. Możliwe, że może to spowodować przejściowy komunikat błędu,
lecz jeśli ostatnio wprowadzono zmiany w konfiguracji lub jeśli otrzymujesz tę
wiadomość więcej niż raz, sprawdź swój certyfikat.

Komunikat o błędzie to '{{$error}}'.   

Przepraszam za utrudnienia, 
	Twój serwer WWW na {{$siteurl}}
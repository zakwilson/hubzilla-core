### Narzędzia migawek portalu

Programiści Hubzilli często muszą przełączać się między gałęziami, które mogą mieć niekompatybilne schematy lub zawartość bazy danych. Poniższe dwa skrypty tworzą i przywracają pełne migawki instancji Hubzilli, w tym zarówno główny katalog sieciowy, jak i stan całej bazy danych. Każdy skrypt wymaga pliku konfiguracyjnego o nazwie *hub-snapshot.conf* znajdującego się w tym samym folderze i zawiera on określone katalogi i szczegóły bazy danych portalu.

### Konfiguracja

Format pliku konfiguracyjnego jest bardzo ścisły. Między nazwą zmiennej a wartością nie może być spacji. Zastąp tylko treść w cudzysłowach swoją konfiguracją. Zapisz ten plik jako *hub-snapshot.conf* obok skryptów.

    # Location of hub root. Typically this is the location of the Hubzilla repo clone.
    HUBROOT="/var/www/"
    # MySQL database name
    DBNAME="hubzilla"
    # MySQL database user
    DBUSER="hubzilla"
    # MySQL database password
    DBPWD="akeufajeuwfb"
    # The target snapshot folder where the git repo will be initialized
    SNAPSHOTROOT="/root/snapshots/hubzilla/"
    
### Migawka

Przykład użycia:

    sh hub-snapshot.sh my-hub.conf "Commit message for the snapshot" 

**hub-snapshot.sh**:

    #!/bin/bash
    
    if ! [ -f "$1" ]; then
    	echo "$1 is not a valid file. Aborting..."
    	exit 1
    fi
    source "$1"
    #echo "$DBNAME"
    #echo "$DBUSER"
    #echo "$DBPWD"
    #echo "$HUBROOT"
    #echo "$SNAPSHOTROOT"
    MESSAGE="snapshot: $2"
    
    if [ "$DBPWD" == "" -o "$SNAPSHOTROOT" == "" -o "$DBNAME" == "" -o "$DBUSER" == "" -o "$HUBROOT" == "" ]; then
    	echo "Required variable is not set. Aborting..."
    	exit 1
    fi
    
    if [ ! -d "$SNAPSHOTROOT"/db/ ]; then
    	mkdir -p "$SNAPSHOTROOT"/db/
    fi
    if [ ! -d "$SNAPSHOTROOT"/www/ ]; then
    	mkdir -p "$SNAPSHOTROOT"/www/
    fi
    
    if [ ! -d "$SNAPSHOTROOT"/www/ ] || [ ! -d "$SNAPSHOTROOT"/db/ ]; then
    	echo "Error creating snapshot directories. Aborting..."
    	exit 1
    fi
    
    echo "Export database..."
    mysqldump -u "$DBUSER" -p"$DBPWD" "$DBNAME" > "$SNAPSHOTROOT"/db/"$DBNAME".sql
    echo "Copy hub root files..."
    rsync -va --delete --exclude=.git* "$HUBROOT"/ "$SNAPSHOTROOT"/www/
    
    cd "$SNAPSHOTROOT"
    
    if [ ! -d ".git" ]; then
    	git init
    fi
    if [ ! -d ".git" ]; then
    	echo "Cannot initialize git repo. Aborting..."
    	exit 1
    fi
    
    git add -A
    echo "Commit hub snapshot..."
    git commit -a -m "$MESSAGE"
    
    exit 0

### Przywracanie

    #!/bin/bash
    # Restore hub to a previous state. Input hub config and commit hash
    
    if ! [ -f "$1" ]; then
            echo "$1 is not a valid file. Aborting..."
            exit 1
    fi
    source "$1"
    COMMIT=$2
    
    if [ "$DBPWD" == "" -o "$SNAPSHOTROOT" == "" -o "$DBNAME" == "" -o "$DBUSER" == "" -o "$HUBROOT" == "" ]; then
            echo "Required variable is not set. Aborting..."
            exit 1
    fi
    RESTOREDIR="$(mktemp -d)/"
    
    if [ ! -d "$RESTOREDIR" ]; then
    	echo "Cannot create restore directory. Aborting..."
    	exit 1
    fi
    echo "Cloning the snapshot repo..."
    git clone "$SNAPSHOTROOT" "$RESTOREDIR"
    cd "$RESTOREDIR"
    echo "Checkout requested snapshot..."
    git checkout "$COMMIT"
    echo "Restore hub root files..."
    rsync -a --delete --exclude=.git* "$RESTOREDIR"/www/ "$HUBROOT"/
    echo "Restore hub database..."
    mysql -u "$DBUSER" -p"$DBPWD" "$DBNAME" < "$RESTOREDIR"/db/"$DBNAME".sql
    
    chown -R www-data:www-data "$HUBROOT"/{store,extend,addon,.htlog,.htconfig.php}
    
    echo "Restored hub to snapshot $COMMIT"
    echo "Removing temporary files..."
    
    rm -rf "$RESTOREDIR"
    
    exit 0


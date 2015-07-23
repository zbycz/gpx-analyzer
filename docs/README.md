# Semestr�ln� pr�ce MI-VMW
## Analyz�r a klasifik�tor GPS z�znam�

**2014 Bc. Pavel Zbytovsk�, FIT �VUT**

## 1) Popis projektu

C�lem projektu je vytvo�it webovou aplikaci, kter� um� zobrazit z�znamy z GPS nad plan�rn�m prostorem, um�t je rychle klasifikovat a proch�zet. Vstupem jsou z�znamy ve form�tu GPX, kter� se nahraj� do webov�ho rozhran�. V�stupem aplikace je zobrazen� stop s provedeou klasifikac� pro snadn� odli�en�.

## 2) Zp�sob �e�en�

Klasifikace s rozli�en�m p��ch, cyklistick�ch a motorov�ch stop m� na starosti metoda `getClassification()`. GPS z�znam se skl�d� z jednotliv�ch bod� s polohopisem, v��ekopisem a �asovou informac� s p�esnost� na vte�iny. Z tohoto d�vodu je nutno nejd��ve seskupit v�ce bod� do skupiny, abychom se vyvarovali vysok� nep�esnosti �asov�ho rozd�lu. Pro ka�dou skupinu je spo��t�na p�esn� vzd�lenost jednotliv�ch mezibod� Haversinov�m vzorcem a spo�ten celkov� �as. Granularita byla zvolena 60 vte�in.

Druh�m krokem je pro�i�t�n� zaru�en�ch chybov�ch skupin. Nap�. p�i del��m zastaven� na m�st� vznik� velk� mno�stv� bod�, kter� se ov�em od prvn�ho p��li� nevzdaluj�. D�le je t�eba odstranit del�� chyb�j�c� z�znam � ten m��e vzniknout pauznut�m zaznamen�v�n� �i ru�en�m sign�lu t�eba okoln�m ter�nem.

T�et�m krokem je samotn� klasifikace, kde se vyu��v� prahov�ho �e�en�. Pokud skupina m� rychlost odpov�daj�c� n�kter� t��d�, je j� p�i�azena a dopo�teny souhrnn� hodnoty �asu a vzd�lenosti pro danou t��du.

## 3) Implementace

Webov� aplikace je postaven� nad jazykem PHP a vyu��v� ke sv�mu b�hu framework Nette 2.2.6 ([http://nette.org](http://nette.org/)). Hlavn� prezentaci mapov�ho obsahu obstar�v� knihovna Leaflet ([http://leafletjs.com](http://leafletjs.com/)). Dopl�kov� skripty v jazyce JavaScript zaji��uj� sou�innost t�chto dvou ��st�.

Hlavn� logika v�po�t� a klasifikace se ukr�v� ve t��d� `GpsPath`, ta na sv�m vstupu dost�v� GPX soubor standardu XML, pro��t� jej a z�sk�v� hodnoty k zobrazen� � nap�. d�lku trasy, p�ev��en�, �i v�b�r bod� pro zobrazen� na map�. O klasifika�n� metod� je pojedn�no v��e.

Pro u�ivatele je p�ipraven pohodln� vsup pro nahr�v�n� GPX soubor� � vyu��v� se knihovny jQuery File Upload Plugin, kter� um� v�cen�sobn� nahr�v�n� ([https://github.com/blueimp/jQuery-File-Upload](https://github.com/blueimp/jQuery-File-Upload)). Zobrazen� pr�b�hu zaji��uje Mini Ajax File Upload Form ([http://tutorialzine.com/2013/05/mini-ajax-file-upload-form/](http://tutorialzine.com/2013/05/mini-ajax-file-upload-form/)).

Po nahr�n� je GPX stopa podrobena rozboru pomoc� t��dy GpsPath a v�sledek je ulo�en do MySQL datab�ze, viz Obr�zek 1.

Po�adavky na b�h: PHP 5.6, MySQL 5.5.27, prohl�e� kompatibiln� HTML5.

![](schema-tabulky-gpx.gif)  
_Obr�zek 1: Sch�ma tabulky gpx_

## 4) P��klad v�stupu

Okno aplikace (Obr�zek 2) obsahuje po lev� stran� seznam v�ech nahran�ch soubor�, po najet� se zazoomuje zaznamenan� stopa na map�. T�matickou vrstvu lze p�epnout v prav�m horn�m rohu.

Klasifikace je zn�zorn�n� ikonou, konkr�tn� v�stup algorimu je pro tento ��dek takov�:

```
{result: bike, car: {treshold: 40, duration: 60.0, length: 673.23801979931}, bike: {treshold: 8, duration: 15721.0, length: 82409.175640864}, walking: {treshold: 2, duration: 1952.0, length: 3085.7008182243}}
```

![](okno-aplikace.gif)  
_Obr�zek 2: Okno v�sledn� aplikace s zobrazen� stopy_

## 5) Experiment�ln� sekce

V�konov� je algoritmus velmi rychl�, asymptoticky O(n), kde n je po�et z�znam� v GPS logu. V�po�etn� se prov�d� pr�v� Haversin�v vzorec:

![](haversine.gif)  

�asov� z�vislost je v Tabulce 1. Pro v�t�� mno�stv� z�znam� jsou ji� �asy docela dlouh�, zde by mohlo pomoci nap��klad p�edzpracov�n� dat do rychlej��ho form�tu ne� XML, �i proudov� zpracov�n�.

 n  | t [ms]
---:| ---:
4 683 | 681
9 479 | 1 126
24 805 | 2 801
36 973 | 5 004

_Tabluka 1: �asov� zavislost pro r�zn� po�ty prvk�_

## 6) Diskuze

Kdy� pomineme hledisko u�ivatelsk�ho rozhran� �i multiu�ivatelsk�ho prost�ed�, vyvst�v� p�ed n�mi p�edev��m ot�zka ukl�d�n� a zpracov�n� geografick�ch dat v gis datab�z�ch. D�ky takov�mu ulo�en� by mohlo b�t mnohem rychlej�� zpracov�n� a t� by se otev�ely obzory k dal�� pr�ci s daty. Nab�z� se t�eba hled�n� spole�n� cesty, a n�sledn� m��en� r�zn�ch dosa�en�ch rychlost� na t�chto segmentech, �i rychl� vykreslen� do rastrov� vrstvy na stran� serveru.

## 7) Z�v�r

Pr�ce �e�ila probl�m klasifikace a zobrazen� gps stop. Povedlo se vytvo�it funk�n� aplikace pro jednou�iatelsk� prost�ed�, kter� tento probl�m �e�� a nab�z� u�ivateli funk�n� z�klad pro spr�vu dat. Aplikace je napsan� na robustn�m z�kladu a tedy umo��uje dal�� rozvoj v budoucnu.



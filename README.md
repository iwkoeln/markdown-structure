# iwm/markdown-structure

## Die Idee

Eine PHP-Library, die einen bestimmten Ordner nach Dokumentations-Dateien (Markdown/MD) abgescannt und
die Struktur bereitstellt.

Zudem können in der Library die Markdown Dateien geparsed werden und darin enthaltene Referenzen
(Links zu anderen Dateien/Bildern) werden überprüft/validiert.


## Vorgehen

- MarkdownProjectFactory wird konfiguriert
  - Pfade
  - Features (enableValidation, etc)
  -
- Raus kommt ein MarkdownProject ValueObject, welches in der weiteren Verarbeitung mit der Doku hilft


## TODOs

- ErrorClasses: Über Objekte
- Validatoren fixen und erweiterbar machen
- Zusätzliche MarkdownProjekt Attribute "MediaFiles" durch zusätzliche geschachtelte if bei der trennung von md und else und als neuen FileObject und nicht nur als string

Eventuell einbauen:
- Bilder und MD Files referenzen zu einander

# Notes from the Royal Botanic Garden Edinburgh

Metadata for Notes from the Royal Botanic Garden Edinburgh.

## Sources

Metadata in MARC XML from Lorna Mitchell (RBGE).

## SQL

Generate SQL to insert PDF file source file, e.g.

```
SELECT DISTINCT CONCAT("UPDATE publications SET pdf='file://Notes_from_RBGE/", volume, "/Notes_from_the_Royal_Botanic_Garden_Edinburgh_Volume_", volume, "_No_", issue, "_(", year, ").pdf#page=22' WHERE issn='0080-4274' and volume=", volume, " and issue='", issue, "';") FROM publications WHERE issn="0080-4274" and volume=45 and issue=1;
```

This creates a local link to the PDF file for an issue, and generates a URL that includes the offset to the first page in the PDF. The convention is that the value of `page=` is the value that when subtracted from the physical page number in the PDF generates the actual page number printed on the page in the PDF.


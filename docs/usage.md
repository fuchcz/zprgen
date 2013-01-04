# USAGE

## ZPRGEN DEFINITION

Every post that should be processed by ZprGen has to include definition at the beginning `[A]`. Where A can be:

* `X` for intro items
* `S` for 'středisko' items
* `7` for 'Poutníci' items
* `8` for 'Lesní skřítci' items

## ZPRAVODAJ DEFINITION

To define Zpravodaj issue, make a post beginning with `[X][MM/YYYY]`, where MM represents month and YYYY represents year.

## GENERAL VS. EVENT ITEMS

Item is classified as an event item if post includes `* [b]sraz:[/b]`.

## SYNTAX

### TEXT EMPHASISE

Bold text can be produced by using `[b]` tag e.g. "This is [b]bold[/b] text.".
To make text italics, use `[i]` tag e.g. "This is [i]italics[/i] text.".
Underlined text can be emphasised by using `[u]` tag. e.g. "This is [u]underlined[/u] text.".

### IMAGES

To insert image use [img]imagename.ext[img]. PNGs and JPGs are allowed. Images are resized to column width. This can be modified by inserting percentage modificator e.g. "This image will be resized to 60% width of th column: [img]image.png|60[/img]".

### TABLES

Table cell is surrounded by `|` sign. For example simple table could look like this:

    | * | Name | Lastname |
    | 1 | John | Brown |
    | 2 | George | Black |

There is few modificators that can adjust table cell appearance. Insert them right after `|` sign in this order:

1.   Merged cells can be created by inserting a number e.g. "|3 This cell is stretched througn 3 cells |".
2.   Align of text can be modified by inserting `l` for left, `r` for right, `c` for center e.g. "| default |c center aligned |r right aligned |".
3.   For inverting cell (i.e. white text on black background) use `i` modfiicator.

### LISTS

Every line started with `*` followed by a space is converted to list item. For example:

    * First item.
    * Second item.

### DATETIME

Single tag `[DD/MM/YYYY HH:MM]` is converted into textual time representation with day name. This tag have to follow list item with beginning and ending statement to be processed - i.e.

    * [b]sraz:[/b] [24/12/2012 18:00] na klubovně

## ZPRAVODAJ GENERATION

To generate complete issue, make post containing `[zprgen:generate]`.

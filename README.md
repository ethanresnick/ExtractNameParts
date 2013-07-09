# ExtractNameParts
### A PHP class to break a full name string into its component parts (i.e. first name, last name, and (optionally) middle initial)

 - Copyright 2012: Ethan Resnick, ethanresnick.com
 - Licensed under the [Do What The Fuck You Want To Public License Version 2 license](http://en.wikipedia.org/wiki/WTFPL). 
 
A lot of websites still ask users for their first and last name in separate form fields. In most cases, I think this is unnecessary and bad for UX. Better to have one "Full Name" field and split the name up behind the scenes. This class does that splitting.

It's built for English names.

Currently it uses a very simple implementation (based mainly on counting the number of words provided, while accounting for standard English patterns like "Last Name, First Name") that's not 100% accurate. While the results could be dramatically improved by, for example, including a dictionary of first and/or last names to check against, achieving 100% certainty would still be hard. Take the input "David Michael Byrd". Is this a first, middle, and last name or is David Michael the fist name (or Michael Byrd the last name)? 

To account for this uncertainty, the class returns a "certainty score" with each result. The designer/developer can then decide if/when to prompt the user further for clarification, depending on the product's needs. This is a great way to implement [gradual engagement](http://www.lukew.com/ff/entry.asp?1128).


Usage Instructions
======

1. Instantiate the <code>ExtractNameParts</code> object, passing in the name to the constructor: <code>$extractor = new ExtractNameParts($nameToSplit);</code>

2. Get the results. <code>$extractor->getNameParts();</code> will return an array with keys 'firstName', 'lastName', and, when it's detected, 'middleInitial'. The certainty score associated with this split is available from <code>$extractor->getCertaintyScore();</code>. An unmodified version name provided is available from <code>$extractor->getUnmodifiedName()</code>, should you want it.

3. If you need to split more than one name, you can simply create a new <code>ExtractNameParts</code> instance.

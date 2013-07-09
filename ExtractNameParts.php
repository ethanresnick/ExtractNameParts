<?php

class ExtractNameParts
{

    /**
     * @var string Holds an unmodified copy of the full name the class was constructed with
     */
    protected $fullName;

    /**
     * @var array A cached version of the name once it's been split up.
     */
    protected $nameParts;

    /**
     * @var float A number between 0 and 1 representing how sure we are that the split is right.
     */
    protected $certaintyScore;

    /**
     * Constructor
     * @param string $fullNameString The string of the full name that you want to
     * split up/operate on. If not provided here, can be set with {@link setFullName()}.
     */
    public function __construct($fullNameString = '')
    {
        $this->fullName = trim((string) $fullNameString);
    }

    protected function processName()
    {
        /**
         * Make the name the proper case.
         * 
         * Start by lowercasing it. Then split it into parts, which we'll properly capitalize.
         * Note that $parts (b/c of the flags) will always have an odd length and be in format:
         * Match Delim Match Delim Match...
         */
        $parts = preg_split('/(\s|\'|\-)/', strtolower($this->fullName), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        
    	//Fix the weird English intra-name capitals like McCain.
        $parts = preg_replace_callback('/^(lo|ma?c)([a-z])/i', $this->intraCapsFix($matches), $parts);

        //Build the name string from the parts, capitalizing the beginning of each part.
        $name = '';
        for ($i = 0, $len = count($parts); $i < $len - 1; $i+=2)
        {
            $name .= ucfirst($parts[$i]) . $parts[$i + 1];
        }
        $name .= ucfirst($parts[$len - 1]);


        /*
         * Find, set, and then strip and middle initial from the name if it's present. That'll
         * leave the name with only a first and last name, so we can then treat it like all the
         * other input that doesn't come with a middle initial. 
         * 
         * 
         * The middle intial regex works like: 
         * 
         * A space or comma (not a word boundary because that would allow the period of the 
         * first part of a name like A.J. to cause the j to match) 
         * 
         * Followed by one letter (but not if that letter is followed by .letter.)  
         * 
         * Followed by an optional period followed by one or more non-word characters 
         * (I've used the expanded version of \W so that i could add ' to the list).
         */
        $pattern = '/(\s|,)([A-Za-z])(?!\.[A-Za-z]\.)\.?([^a-zA-Z0-9_\'])+/';
        $matches = array();

        if (preg_match($pattern, $name, $matches))
        {
            //[2] is the letter, w/o the dot.
            $this->nameParts['middleInitial'] = $matches[2];

            //keep a space because it's trimmable later but may be necessary for parsing.
            $name = preg_replace($pattern, ' ', $name);
        }


        /*
         * Now handle first and last names.
         * 
         * In the first if, we have a comma separating them, so we can safely assume a 
         * "First, Last" format.
         * 
         * In the else, we may simply have Word Word, in which case we can safely assume
         * "First Last" format. But if we have more than two words, we have to guess.
         */
        if (strpos($name, ','))
        {
            $pieces = split(',', $name);

            $this->nameParts['lastName'] = trim($pieces[0]);
            $this->nameParts['firstName'] = trim($pieces[1]);
            $this->certaintyScore = .99;
        }
        else
        {
            $pieces = explode(" ", $name);

            switch (count($pieces))
            {
                case 1:
                    $this->nameParts['firstName'] = trim($pieces[0]);
                    $this->nameParts['lastName'] = null;
                    $this->certaintyScore = .75;
                    break;

                case 2:
                    $this->nameParts['firstName'] = trim($pieces[0]);
                    $this->nameParts['lastName'] = trim($pieces[1]);
                    $this->certaintyScore = .95;
                    break;

                case 3:
                    $this->nameParts['firstName'] = trim($pieces[0] . ' ' . $pieces[1]);
                    $this->nameParts['lastName'] = trim($pieces[2]);
                    $this->certaintyScore = .4;
                    break;

                default:
                    $this->nameParts['firstName'] = trim($pieces[0]);
                    array_shift($pieces); //shift the first name off
                    //String the remaining name pieces together with spaces
                    $this->nameParts['lastName'] = trim(implode(' ', $pieces));
                    $this->certaintyScore = .4;
            }
        }

        // Capitalizes two-letter words (optionally with periods), like CC or A.J.
        $len = strlen($this->nameParts['firstName']);
        if (($len == 2) || ($len == 4 && $this->nameParts['firstName'][1] == '.' && $this->nameParts['firstName'][3] == '.'))
        {
            $this->nameParts['firstName'] = strtoupper($this->nameParts['firstName']);
        }
    }

    /**
     * Called from preg_replace_callback in {@link processName()}. Don't call directly.
     * "Should" be defined as an anonymous function inline, but I don't want to require PHP 5.3
     */
    private function intraCapsFix($matches)
    {
        return $matches[1].ucfirst($matches[2]);
    }
    
    public function getUnmodifiedName()
    {
        return $this->fullName;
    }

    public function getNameParts()
    {
        if ($this->nameParts == NULL) {
            $this->processName();
        }
        
        return $this->nameParts;
    }

    public function getCertaintyScore()
    {
        if($this->nameParts == NULL) {
            $this->processName();
        }

        return $this->certaintyScore;
    }

}

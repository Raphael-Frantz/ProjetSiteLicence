<?php
// *****************************************************************************************************
// * Class used to represent an user.
// *****************************************************************************************************
class User {

    // Constants used to generate random secure passwords
    const KEYSPACE = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const SYMBOLSPACE = '{}@[]+-*_#&=';
    const PASSWORD_LENGTH = 10;
    const SYMBOLS_NUMBER = 2;
    
    private $id;                                 // Identifier
	private $name;                               // Name
	private $firstname;                          // Firstname
    private $mail;                               // Mail address
    private $password;                           // Password
    private $mailDate;                           // Date of the last password recovery mail (to avoid spam)
    private $key;                                // Key of the last password recovery
    private $active;                             // If 'true', the user is active
    private $type;                               // Type de l'utilisateur (TYPE_*)
    
    // *****************************************************************************************************
    // * Specific for users types
    // *****************************************************************************************************

    const TYPE_UNDEF = -1;                       // Type indéfini
    const TYPE_ADMIN = 1;                        // Type admin
    const TYPE_ENSEIGNANT = 2;                   // Type enseignant
    const TYPE_ETUDIANT = 3;                     // Type étudiant

    /**
     * Create a new user.
     * @param id the identifier
     * @param name the name
     * @param firstname the firstname
     * @param mail the mail address
     * @param password the password
     * @param type the type of the user
     * @param mailDate the date of the last password recovery mail
     * @param key the key for the password recovery
     * @param active if 'true', the user is active
     */
	function __construct(int $id = -1, string $name = "", string $firstname = "", string $mail = "", 
                         string $password = "", int $type = TYPE_UNDEF,
                         int $mailDate = 0, string $key = "", bool $active = true) {
		$this->id = $id;
		$this->name = $name;
		$this->firstname = $firstname;
        $this->mail = $mail;
        $this->password = $password;
        $this->mailDate = $mailDate;
        $this->key = $key;
        $this->active = $active;
        $this->type = $type;
	}
    
    /**
     * Return the identifier.
     * @return the identifier
     */
    public function getId() :int {
        return $this->id;
    }
    
    /**
     * Modifie l'identifiant.
     * @param id l'identifiant
     */
    public function setId(int $id) {
        $this->id = $id;
    }     
    
    /**
     * Return the name
     * @return the name
     */
    public function getName() : string {
        return $this->name;
    }

    /**
     * Return the firstname
     * @return the firstname
     */
    public function getFirstname() : string {
        return $this->firstname;
    }
    
    /**
     * Return the mail address.
     * @return the mail address
     */
    public function getMail() : string {
        return $this->mail;
    }

    /**
     * Return the password.
     * @return the password
     */
    public function getPassword() : string {
        return $this->password;
    }    
    
    /**
     * Set the password.
     * @param password the new password
     */
    public function setPassword(string $password) {
        $this->password = $password;
    }    
    
    /**
     * Return the date of the last password recovery mail.
     * @return the date of the last password recovery mail.
     */
    public function getMailDate() : int {
        return $this->mailDate;
    }   
    
    /**
     * Return the key of the last password recovery.
     * @return the key of the last password recovery.
     */
    public function getKey() : string {
        return $this->key;
    }
    
    /**
     * Return if the user is active or not.
     * @return 'true' if the user is active else return 'false'
     */
    public function isActive() : bool {
        return $this->active;
    }
    
    /**
     * Retourne the type.
     * @return the type
     */
    public function getType() : string {
        return $this->type;
    }    
    
    /**
     * Convert user to a string.
     * @return a string
     */
    public function __toString() : string {
        return $this->firstname." ".$this->name;
    }
    
    /**
     * Generate a random password.
     * @return a random password
     */
    public static function generatePassword() : string {
        $result = '';
        $max = strlen(self::KEYSPACE) - 1;
        for ($i = 0; $i < self::PASSWORD_LENGTH - self::SYMBOLS_NUMBER; ++$i)
            $result .= self::KEYSPACE[random_int(0, $max)];
        
        $max = strlen(self::SYMBOLSPACE) - 1;
        for ($i = 0; $i < self::SYMBOLS_NUMBER; ++$i)
            $result = substr_replace($result, self::SYMBOLSPACE[random_int(0, $max)], random_int(0, strlen($result) - 1), 0);
            
        return $result;
    }

} // class User
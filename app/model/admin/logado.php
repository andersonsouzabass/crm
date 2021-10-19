<?php
/**
 * SystemUser
 *
 * @version    1.0
 * @package    model
 *
 * @author     Anderson Souza
 */
class Logado extends TRecord
{
    const TABLENAME = 'system_user';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    // use SystemChangeLogTrait;
    
    private $frontpage;
    /*
    private $unit;
    private $system_user_groups = array();
    private $system_user_programs = array();
    private $system_user_units = array();
    private $system_user_fornecedores = array();
    */

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('logado');
        parent::addAttribute('data_logado');
        parent::addAttribute('hora_logado');
        parent::addAttribute('data_logout');
        parent::addAttribute('hora_logout');
        parent::addAttribute('id_user');
    }
       
    
    /**
     * Validate user login
     * @param $login String with user login
     */
    public static function validate($login)
    {
        $user = self::newFromLogin($login);
        
        if ($user instanceof SystemUser)
        {
            if ($user->active == 'não')
            {
                throw new Exception(_t('Inactive user'));
            }
        }
        else
        {
            throw new Exception(_t('User not found'));
        }
        
        return $user;
    }
    
    /**
     * Authenticate the user
     * @param $login String with user login
     * @param $password String with user password
     * @returns TRUE if the password matches, otherwise throw Exception
     */
    public static function authenticate($login, $password)
    {
        $user = self::newFromLogin($login);
        if (!hash_equals($user->password, md5($password)))
        {
            throw new Exception(_t('Wrong password'));
        }
        
        return $user;
    }
    
    /**
     * Returns a SystemUser object based on its login
     * @param $login String with user login
     */
    static public function newFromLogin($login)
    {
        return SystemUser::where('login', '=', $login)->first();
    }
    
    /**
     * Returns a SystemUser object based on its e-mail
     * @param $email String with user email
     */
    static public function newFromEmail($email)
    {
        return SystemUser::where('email', '=', $email)->first();
    }
    
    /**
     * Return the programs the user has permission to run
     */
    public function getPrograms()
    {
        $programs = array();
        
        foreach( $this->getSystemUserGroups() as $group )
        {
            foreach( $group->getSystemPrograms() as $prog )
            {
                $programs[$prog->controller] = true;
            }
        }
                
        foreach( $this->getSystemUserPrograms() as $prog )
        {
            $programs[$prog->controller] = true;
        }
        
        return $programs;
    }
    
    /**
     * Return the programs the user has permission to run
     */
    public function getProgramsList()
    {
        $programs = array();
        
        foreach( $this->getSystemUserGroups() as $group )
        {
            foreach( $group->getSystemPrograms() as $prog )
            {
                $programs[$prog->controller] = $prog->name;
            }
        }
                
        foreach( $this->getSystemUserPrograms() as $prog )
        {
            $programs[$prog->controller] = $prog->name;
        }
        
        asort($programs);
        return $programs;
    }


     /**
     * Return the fornecedor the user has permission to run
     */
    public function getFornecedor()
    {
        $fornecedor = array();
                        
        foreach( $this->getUsuarioFornecedor() as $fornec )
        {
            $fornecedor[$fornec->controller] = true;
        }
        
        return $fornecedor;
    }
    
    /*
     * Return the fornecedores the user has permission to run
    */
    public function getFornecedorList()
    {
        $fornecedores = array();
        
        foreach( $this->getUsuarioFornecedor() as $fornec )
        {
            $fornecedores[$fornec->controller] = $fornec->name;
        }
        
        asort($fornecedores);
        return $fornecedores;
    }
    
    /**
     * Check if the user is within a group
     */
    public function checkInGroup( SystemGroup $group )
    {
        $user_groups = array();
        foreach( $this->getSystemUserGroups() as $user_group )
        {
            $user_groups[] = $user_group->id;
        }
    
        return in_array($group->id, $user_groups);
    }
    
    /**
     *
     */
    public static function getInGroups( $groups )
    {
        $collection = [];
        $users = self::all();
        if ($users)
        {
            foreach ($users as $user)
            {
                foreach ($groups as $group)
                {
                    if ($user->checkInGroup($group))
                    {
                        $collection[] = $user;
                    }
                }
            }
        }
        return $collection;
    }
}

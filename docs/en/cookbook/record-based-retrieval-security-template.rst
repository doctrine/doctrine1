========================================
Record-based Retrieval Security Template
========================================

------------
Introduction
------------

This is a tutorial & how-to on using a security template and listener to
restrict a user to specific records, or a range of specific records
based on credentials and a user table association. Basically fine
grained user access control.

This template was created for a project which had a few credentials,
division\_manager, district\_manager, branch\_manager, and salesperson.
We have a list of accounts, their related sales and all sorts of
sensitive information for each account. Each logged in user should be
allowed to only view the accounts and related information based off
their credentials + either the division, district, branch or salesperson
they are allowed to view.

So a division manager can view all info for all accounts within his
division. A salesperson can only view the accounts they are assign.

The template has been a work in progress so the code below may not
actually be the final code I'm using today. But since it is now working
for all situations I'm asking of it, I thought I would post it as is.

--------
Template
--------

 class gsSecurityTemplate extends Doctrine\_Template { protected
$\_options = array();

::

    /**
     * __construct
     *
     * @param string $options
     * @return void
     */
    public function __construct(array $options)
    {
        if (!isset($options['conditions']) || empty($options['conditions'])) {
            throw new Doctrine_Exception('Unable to create security template without conditions');
        }

        $this->_options = $options;
    }

    public function setUp()
    {
        $this->addListener(new gsSecurityListener($this->_options));
    }

}

class gsSecurityListener extends Doctrine\_Record\_Listener { private
static $*user\_id = 0, $*credentials = array(), $\_alias\_count = 30;

::

    protected $_options = array();

    /**
     * __construct
     *
     * @param string $options
     * @return void
     */
    public function __construct(array $options)
    {
        $this->_options = $options;
    }

    public function preDqlSelect(Doctrine_Event $event)
    {
        $invoker = $event->getInvoker();
        $class   = get_class($invoker);
        $params  = $event->getParams();

        if($class == $params['alias']) {
            return;
        }

        $q       = $event->getQuery();

        // only apply to the main protected table not chained tables... may break some situations
        if(!$q->contains('FROM '.$class)) {
            return;
        }

        $wheres = array();
        $pars   = array();

        $from = $q->getDqlPart('from');

        foreach ($this->_options['conditions'] as $rel_name => $conditions) {
            $apply = false;
            foreach ($conditions['apply_to'] as $val) {
                if (in_array($val,self::$_credentials)) {
                    $apply = true;
                    break;
                }
            }

            if ($apply) {
                $alias = $params['alias'];
                $aliases = array();
                $aliases[] = $alias;

                foreach ($conditions['through'] as $key => $table) {
                    $index = 0;
                    $found = false;
                    foreach ($from as $index => $val) {
                        if (strpos($val,$table) !== false) {
                            $found = true;
                            break;
                        }

                    }

                    if ($found) {
                        $vals = explode(' ', substr($from[$index],strpos($from[$index],$table)));
                        $alias = (count($vals) == 2) ? $vals[1]:$vals[0];
                        $aliases[] = $alias;
                    } else {
                        $newalias = strtolower(substr($table,0,3)).self::$_alias_count++;
                        $q->leftJoin(end($aliases).'.'.$table.' '.$newalias);
                        $aliases[] = $newalias;
                    }
                }

                $wheres[] = '('.end($aliases).'.'.$conditions['field'].' = ? )';
                $pars[] = self::$_user_id;
            }
        }

        if(!empty($wheres)) {
            $q->addWhere( '('.implode(' OR ',$wheres).')',$pars);
        }
    }

    static public function setUserId($id)
    {
        self::$_user_id = $id;
    }

    static public function setCredentials($vals)
    {
        self::$_credentials = $vals;
    }

}

------------------
YAML schema syntax
------------------

Here is the schema I used this template with. I've removed lots of extra
options, other templates I was using, indexes and table names. It may
not work out of the box without the indexes - YMMV.

Account: actAs: gsSecurityTemplate: conditions: Division: through: [
Division, UserDivision ] field: user\_id apply\_to: [ division\_manager
] Branch: through: [ Branch, UserBranch ] field: user\_id apply\_to: [
branch\_manager ] Salesperson: through: [ Salesperson, UserSalesperson ]
field: user\_id apply\_to: [ salesperson ] District: through: [ Branch,
District, UserDistrict ] field: user\_id apply\_to: [ district\_manager
] columns: id: { type: integer(4), primary: true, autoincrement: true,
unsigned: true } parent\_id: { type: integer(4), primary: false,
autoincrement: false, unsigned: true} business\_class\_id: { type:
integer(2), unsigned: true } salesperson\_id: { type: integer(4),
unsigned: true } branch\_id: { type: integer(4), unsigned: true }
division\_id: { type: integer(1), unsigned: true } sold\_to: { type:
integer(4), unsigned: true }

Division: columns: id: { type: integer(1), autoincrement: true, primary:
true, unsigned: true } name: { type: string(32) } code: { type:
string(4) }

District: actAs: gsSecurityTemplate: conditions: Division: through: [
Division, UserDivision ] field: user\_id apply\_to: [ division\_manager
] relations: Division: foreignAlias: Districts local: division\_id
onDelete: RESTRICT columns: id: { type: integer(4), autoincrement: true,
primary: true, unsigned: true } name: { type: string(64) } code: { type:
string(4) } division\_id: { type: integer(1), unsigned: true }

Branch: actAs: gsSecurityTemplate: conditions: Division: through: [
Division, UserDivision ] field: user\_id apply\_to: [ division\_manager
] District: through: [ District, UserDistrict ] field: user\_id
apply\_to: [ district\_manager ] relations: Division: local:
division\_id foreignAlias: Branches onDelete: CASCADE District:
foreignAlias: Branches local: district\_id onDelete: RESTRICT columns:
id: { type: integer(4), primary: true, autoincrement: true, unsigned:
true } name: { type: string(64) } code: { type: string(4) }
district\_id: { type: integer(4), unsigned: true } division\_id: { type:
integer(1), unsigned: true } is\_active: { type: boolean, default: true
}

User: relations: Divisions: class: Division refClass: UserDivision
local: user\_id foreign: division\_id Districts: class: District
refClass: UserDistrict local: user\_id foreign: district\_id Branches:
class: Branch refClass: UserBranch local: user\_id foreign: branch\_id
Salespersons: class: Salesperson refClass: UserSalesperson local:
user\_id foreign: salespersons\_id columns: id: { type: integer(4),
autoincrement: true, primary: true, unsigned: true } name: { type:
string(128) } is\_admin: { type: boolean, default: false } is\_active: {
type: boolean, default: true } is\_division\_manager: { type: boolean,
default: false } is\_district\_manager: { type: boolean, default: false
} is\_branch\_manager: { type: boolean, default: false }
is\_salesperson: { type: boolean, default: false } last\_login: { type:
timestamp }

UserDivision: tableName: user\_divisions columns: id: { type:
integer(4), autoincrement: true, primary: true, unsigned: true }
user\_id: { type: integer(4), primary: true, unsigned: true }
division\_id: { type: integer(1), primary: true, unsigned: true }

UserDistrict: tableName: user\_districts columns: id: { type:
integer(4), autoincrement: true, primary: true, unsigned: true }
user\_id: { type: integer(4), primary: true, unsigned: true }
district\_id: { type: integer(4), primary: true, unsigned: true }

UserBranch: tableName: user\_branches columns: id: { type: integer(4),
autoincrement: true, primary: true, unsigned: true } user\_id: { type:
integer(4), primary: true, unsigned: true } branch\_id: { type:
integer(4), primary: true, unsigned: true }

UserSalesperson: tableName: user\_salespersons columns: id: { type:
integer(4), autoincrement: true, primary: true, unsigned: true }
user\_id: { type: integer(4), primary: true, unsigned: true }
salespersons\_id: { type: integer(4), primary: true, unsigned: true }

You can see from the User model that the credentials are set within the
db. All hardcoded in this situation.

------------------
Using the template
------------------

Once you've built your models from the schema, you should see something
like the following in your model's setUp function.

 $gssecuritytemplate0 = new gsSecurityTemplate(array('conditions' =>
array('Division' => array( 'through' => array( 0 => 'Division', 1 =>
'UserDivision', ), 'field' => 'user\_id', 'apply\_to' => array( 0 =>
'division\_manager', ), 'exclude\_for' => array( 0 => 'admin', ), ),
'Branch' => array( 'through' => array( 0 => 'Branch', 1 => 'UserBranch',
), 'field' => 'user\_id', 'apply\_to' => array( 0 => 'branch\_manager',
), 'exclude\_for' => array( 0 => 'admin', 1 => 'division\_manager', 2 =>
'district\_manager', ), ), 'Salesperson' => array( 'through' => array( 0
=> 'Salesperson', 1 => 'UserSalesperson', ), 'field' => 'user\_id',
'apply\_to' => array( 0 => 'salesperson', ), 'exclude\_for' => array( 0
=> 'admin', 1 => 'division\_manager', 2 => 'district\_manager', 3 =>
'branch\_manager', ), ), 'District' => array( 'through' => array( 0 =>
'Branch', 1 => 'District', 2 => 'UserDistrict', ), 'field' =>
'user\_id', 'apply\_to' => array( 0 => 'district\_manager', ),
'exclude\_for' => array( 0 => 'admin', 1 => 'division\_manager', ),
)))); :code:`this->actAs(`\ gssecuritytemplate0);

The last part you need to use is to provide the template with the
running user's credentials and id. In my project's session bootstrapping
I have the following ( I use the symfony MVC framework ).

 public function initialize($context,
:code:`parameters = null) { parent::initialize(`\ context,
:code:`parameters = null); gsSecurityListener::setUserId(`\ this->getAttribute('user\_id'));
gsSecurityListener::setCredentials($this->listCredentials());

}

This provides the credentials the user was given when they logged in as
well as their id.

----------
User setup
----------

In my case, I create users and provide a checkbox for their credentials,
one for each type I have. Lets take Division Manager as an example. In
my case we have 3 divisions, East, Central, West. When I create a user I
assign it the West division, and check off that they are a division
manager. I can then proceed to login, and my account listing page will
restrict the accounts I see automatically to my division.

--------
Querying
--------

Now if you query the Account model, the template is triggered and based
on your credentials the results will be restricted.

The query below

 $accounts = Doctrine\_Query::create()->from('Account
a')->leftJoin('a.Branches b')->where('a.company\_name LIKE
?','A%')->execute();

produces the resulting sql.

 SELECT ... FROM accounts a2 LEFT JOIN branches b2 ON a2.branch\_id =
b2.id LEFT JOIN divisions d2 ON a2.division\_id = d2.id LEFT JOIN
user\_divisions u2 ON d2.id = u2.division\_id WHERE a2.company\_name
LIKE ? AND u2.user\_id = ? ORDER BY a2.company\_name

The results you get back will always be restricted to the division you
have been assigned. Since in our schema we've defined restrictions on
the Branch and Districts as well if I were to want to provide a user
with a drop down of potential branches, I can simply query the branches
as I normally would, and only the ones in my division would be returned
to choose from.

------------
Restrictions
------------

For the time being, this module only protects tables in the FROM clause,
since doctrine currently runs the query listener for the new tables
added to the query by the template, and thus we get a pretty nasty query
in the end that doesn't work. If I can figure out how to detect such
situations reliably I'll update the article.

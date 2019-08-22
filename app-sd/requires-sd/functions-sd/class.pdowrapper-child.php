<?php

class PdoWrapperChild extends PdoWrapper {
    
    /**
     * PHP Statement Handler
     *
     * @var object
     */
    private $_oSTH = null;
    /**
     * PDO SQL Statement
     *
     * @var string
     */
    public $sSql = '';
    /**
     * PDO SQL table name
     *
     * @var string
     */
    public $sTable = array();
    /**
     * PDO SQL Where Condition
     *
     * @var string
     */
    public $aWhere = array();
    /**
     * PDO SQL table column
     *
     * @var string
     */
    public $aColumn = array();
    /**
     * PDO SQL Other condition
     *
     * @var string
     */
    public $sOther = array();
    /**
     * PDO Results,Fetch All PDO Results array
     *
     * @var array
     */
    public $aResults = array();
    /**
     * PDO Result,Fetch One PDO Row
     *
     * @var array
     */
    public $aResult = array();
    /**
     * Get PDO Last Insert ID
     *
     * @var integer
     */
    public $iLastId = 0;
    /**
     * PDO last insert di in array
     * using with INSERT BATCH Query
     *
     * @var array
     */
    public $iAllLastId = array();
    /**
     * Get PDO Error
     *
     * @var string
     */
    public $sPdoError = '';
    /**
     * Get All PDO Affected Rows
     *
     * @var integer
     */
    public $iAffectedRows = 0;
    /**
     * Catch temp data
     * @var null
     */
    public $aData = null;
    /**
     * Enable/Disable class debug mode
     *
     * @var boolean
     */
    public $log = false;
    /**
     * Set flag for batch insert
     * @var bool
     */
    public $batch = false;
    /**
     * PDO Error File
     *
     * @var string
     */
    const ERROR_LOG_FILE = 'PDO_Errors.log';
    /**
     * PDO SQL log File
     *
     * @var string
     */
    const SQL_LOG_FILE = 'PDO_Sql.log';
    /**
     * PDO Config/Settings
     *
     * @var array
     */
    private $db_info = array();
    /**
     * Set PDO valid Query operation
     *
     * @var array
     */
    private $aValidOperation = array( 'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'SHOW');
    /**
     * PDO Object
     *
     * @var object
     */
    protected static $oPDO = null;
    
    /**
     * Execute PDO Query
     *
     * @param string $sSql
     * @param array Bind Param Value
     *
     * @return PdoWrapper|multi type:|number
     */
    public function pdoQuery( $sSql = '', $aBindWhereParam = array() ) {
        // clean query from white space
        $sSql         = trim( $sSql );
        // get operation type
        $operation    = explode( ' ', $sSql );
        // make first word in uppercase
        $operation[0] = strtoupper( $operation[0] );
        // check valid sql operation statement
        if ( !in_array( $operation[0], $this->aValidOperation ) ) {
            self::error( 'invalid operation called in query. use only ' . implode( ', ', $this->aValidOperation ) );
        }
        // sql query pass with no bind param
        if ( !empty( $sSql ) && count( $aBindWhereParam ) <= 0 ) {
            // set class property with pass value
            $this->sSql  = $sSql;
            // set class statement handler
            $this->_oSTH = $this->prepare( $this->sSql );
            // try catch block start
            try {
                // execute pdo statement
                if ( $this->_oSTH->execute() ) {
                    // get affected rows and set it to class property
                    $this->iAffectedRows = $this->_oSTH->rowCount();
                    // set pdo result array with class property
                    $this->aResults      = $this->_oSTH->fetchAll();
                    // close pdo cursor
                    $this->_oSTH->closeCursor();
                    // return pdo result
                    return $this;
                } else {
                    // if not run pdo statement sed error
                    self::error( $this->_oSTH->errorInfo() );
                }
            }
            catch ( PDOException $e ) {
                self::error( $e->getMessage() . ': ' . __LINE__ );
            } // end try catch block
        } // if query pass with bind param 
        else if ( !empty( $sSql ) && count( $aBindWhereParam ) > 0 ) {
            // set class property with pass query
            $this->sSql   = $sSql;
            // set class where array
            $this->aData = $aBindWhereParam;
            // set class pdo statement handler
            $this->_oSTH  = $this->prepare( $this->sSql );
            // start binding fields
            // bind pdo param
            $this->_bindPdoParam( $aBindWhereParam );
            // use try catch block to get pdo error
            try {
                // run pdo statement with bind param
                if ( $this->_oSTH->execute() ) {
                    // check operation type
                    switch ( $operation[0] ):
                        case 'SELECT':
                            // get affected rows by select statement
                            $this->iAffectedRows = $this->_oSTH->rowCount();
                            // get pdo result array
                            $this->aResults      = $this->_oSTH->fetchAll();
                            // return PDO instance
                            return $this;
                            break;
                        case 'INSERT':
                            // return last insert id
                            $this->iLastId = $this->lastInsertId();
                            // return PDO instance
                            return $this;
                            break;
                        case 'UPDATE':
                            // get affected rows
                            $this->iAffectedRows = $this->_oSTH->rowCount();
                            // return PDO instance
                            return $this;
                            break;
                        case 'DELETE':
                            // get affected rows
                            $this->iAffectedRows = $this->_oSTH->rowCount();
                            // return PDO instance
                            return $this;
                            break;
                    endswitch;
                    // close pdo cursor
                    $this->_oSTH->closeCursor();
                } else {
                    self::error( $this->_oSTH->errorInfo() );
                }
            }
            catch ( PDOException $e ) {
                self::error( $e->getMessage() . ': ' . __LINE__ );
            } // end try catch block to get pdo error
        } else {
            self::error( 'Query is empty..' );
        }
    }
    /**
     * MySQL SELECT Query/Statement
     *
     * @param string $sTable
     * @param array $aColumn
     * @param array $aWhere
     * @param string $sOther
     *
     * @return multi type: array/error
     */
    public function select( $sTable = '', $aColumn = array(), $aWhere = array(), $sOther = '', $join = false ) {
        // handle column array data
        if(!is_array($aColumn))$aColumn = array();
        // get field if pass otherwise use *
        $sField = count( $aColumn ) > 0 ? implode( ', ', $aColumn ) : '*';
		$sTable = $join == false ? '`'.$sTable.'`' : $sTable;
        // check if table name not empty
        if ( !empty( $sTable ) ) {
            // if more then 0 array found in where array
            if ( count( $aWhere ) > 0 && is_array( $aWhere ) ) {
                // set class where array
                $this->aData = $aWhere;
                // parse where array and get in temp var with key name and val
				$tmp1 = $this->customWhere($this->aData);
				$sWhere = $tmp1['where'];
                // unset temp var
                unset( $tmp );
                // set class sql property
                $this->sSql = "SELECT $sField FROM $sTable WHERE $sWhere $sOther;";
            } else {
                // if no where condition pass by user
                $this->sSql = "SELECT $sField FROM $sTable $sOther;";
            }
            // pdo prepare statement with sql query
            $this->_oSTH = $this->prepare( $this->sSql );
            // if where condition has valid array number
            if ( count( $aWhere ) > 0 && is_array( $aWhere ) ) {
               // bind pdo param
               $this->_bindPdoNameSpace( $aWhere );
            } // if end here
            // use try catch block to get pdo error
            try {
                // check if pdo execute
                if ( $this->_oSTH->execute() ) {
                    // set class property with affected rows
                    $this->iAffectedRows = $this->_oSTH->rowCount();
                    // set class property with sql result
                    $this->aResults      = $this->_oSTH->fetchAll();
                    // close pdo
                    $this->_oSTH->closeCursor();
                    // return self object
                    return $this;
                } else {
                    // catch pdo error
                    self::error( $this->_oSTH->errorInfo() );
                }
            }
            catch ( PDOException $e ) {
                // get pdo error and pass on error method
                self::error( $e->getMessage() . ': ' . __LINE__ );
            } // end try catch block to get pdo error
        } // if table name empty 
        else {
            self::error( 'Table name not found..' );
        }
    }
    /**
     * Execute PDO Insert
     *
     * @param string $sTable
     * @param array $aData
     *
     * @return number last insert ID
     */
    public function insert( $sTable, $aData = array() ) {
        // check if table name not empty
        if ( !empty( $sTable ) ) {
            // and array data not empty
            if ( count( $aData ) > 0 && is_array( $aData ) ) {
                // get array insert data in temp array
                foreach ( $aData as $f => $v ) {
                    $tmp[] = ":s_$f";
                }
                // make name space param for pdo insert statement
                $sNameSpaceParam = implode( ',', $tmp );
                // unset temp var
                unset( $tmp );
                // get insert fields name
                $sFields     = implode( ',', array_keys( $aData ) );
                // set pdo insert statement in class property
                $this->sSql  = "INSERT INTO `$sTable` ($sFields) VALUES ($sNameSpaceParam);";
                // pdo prepare statement
                $this->_oSTH = $this->prepare( $this->sSql );
                // set class where property with array data
                $this->aData = $aData;
                // bind pdo param
                $this->_bindPdoNameSpace( $aData );
                // use try catch block to get pdo error
                try {
                    // execute pdo statement
                    if ( $this->_oSTH->execute() ) {
                        // set class property with last insert id
                        $this->iLastId = $this->lastInsertId();
                        // close pdo
                        $this->_oSTH->closeCursor();
                        // return this object
                        return $this;
                    } else {
                        self::error( $this->_oSTH->errorInfo() );
                    }
                }
                catch ( PDOException $e ) {
                    // get pdo error and pass on error method
                    self::error( $e->getMessage() . ': ' . __LINE__ );
                }
            } else {
                self::error( 'Data not in valid format..' );
            }
        } else {
            self::error( 'Table name not found..' );
        }
    }
    /**
     * Execute PDO Insert as Batch Data
     *
     * @param string $sTable mysql table name
     * @param array $aData mysql insert array data
     * @param boolean $safeModeInsert set true if want to use pdo bind param
     *
     * @return number last insert ID
     */
    public function insertBatch( $sTable, $aData = array(), $safeModeInsert = true ) {
        // PDO transactions start
        $this->start();
        // check if table name not empty
        if ( !empty( $sTable ) ) {
            // and array data not empty
            if ( count( $aData ) > 0 && is_array( $aData ) ) {
                // get array insert data in temp array
                foreach ( $aData[0] as $f => $v ) {
                    $tmp[] = ":s_$f";
                }
                // make name space param for pdo insert statement
                $sNameSpaceParam = implode( ', ', $tmp );
                // unset temp var
                unset( $tmp );
                // get insert fields name
                $sFields = implode( ', ', array_keys( $aData[0] ) );
                // handle safe mode. If it is set as false means user not using bind param in pdo
                if ( !$safeModeInsert ) {
                    // set pdo insert statement in class property
                    $this->sSql = "INSERT INTO `$sTable` ($sFields) VALUES ";
                    foreach ( $aData as $key => $value ) {
                        $this->sSql .= '(' . "'" . implode( "', '", array_values( $value ) ) . "'" . '), ';
                    }
                    $this->sSql  = rtrim( $this->sSql, ', ' );
                    // return this object
                    // return $this;
                    // pdo prepare statement
                    $this->_oSTH = $this->prepare( $this->sSql );
                    // start try catch block
                    try {
                        // execute pdo statement
                        if ( $this->_oSTH->execute() ) {
                            // store all last insert id in array
                            $this->iAllLastId[] = $this->lastInsertId();
                        } else {
                            self::error( $this->_oSTH->errorInfo() );
                        }
                    }
                    catch ( PDOException $e ) {
                        // get pdo error and pass on error method
                        self::error( $e->getMessage() . ': ' . __LINE__ );
                        // PDO Rollback
                        $this->back();
                    } // end try catch block
                    // PDO Commit
                    $this->end();
                    // close pdo
                    $this->_oSTH->closeCursor();
                    // return this object
                    return $this;
                }
                // end here safe mode
                // set pdo insert statement in class property
                $this->sSql  = "INSERT INTO `$sTable` ($sFields) VALUES ($sNameSpaceParam);";
                // pdo prepare statement
                $this->_oSTH = $this->prepare( $this->sSql );
                // set class property with array
                $this->aData = $aData;
                // set batch insert flag true
                $this->batch = true;
                // parse batch array data
                foreach ( $aData as $key => $value ) {
                    // bind pdo param
                    $this->_bindPdoNameSpace( $value );
                    try {
                        // execute pdo statement
                        if ( $this->_oSTH->execute() ) {
                            // set class property with last insert id as array
                            $this->iAllLastId[] = $this->lastInsertId();
                        } else {
                            self::error( $this->_oSTH->errorInfo() );
                            // on error PDO Rollback
                            $this->back();
                        }
                    }
                    catch ( PDOException $e ) {
                        // get pdo error and pass on error method
                        self::error( $e->getMessage() . ': ' . __LINE__ );
                        // on error PDO Rollback
                        $this->back();
                    }
                }
                // fine now PDO Commit
                $this->end();
                // close pdo
                $this->_oSTH->closeCursor();
                // return this object
                return $this;
            } else {
                self::error( 'Data not in valid format..' );
            }
        } else {
            self::error( 'Table name not found..' );
        }
    }
    /**
     * Execute PDO Update Statement
     * Get No OF Affected Rows updated
     *
     * @param string $sTable
     * @param array $aData
     * @param array $aWhere
     * @param string $sOther
     *
     * @return number
     */
    public function update( $sTable = '', $aData = array(), $aWhere = array(), $sOther = '' ) {
        // if table name is empty
        if ( !empty( $sTable ) ) {
            // check if array data and where array is more then 0
            if ( count( $aData ) > 0 && count( $aWhere ) > 0 ) {
                // parse array data and make a temp array
                foreach ( $aData as $k => $v ) {
                    $tmp[] = "$k = :s_$k";
                }
                // join temp array value with ,
                $sFields = implode( ', ', $tmp );
                // delete temp array from memory
                unset( $tmp );
                // parse where array and store in temp array
                foreach ( $aWhere as $k => $v ) {
                    $tmp[] = "$k = :s_$k";
                }
                $this->aData = $aData;
                $this->aWhere = $aWhere;
                // join where array value with AND operator and create where condition
                $sWhere = implode( ' AND ', $tmp );
                // unset temp array
                unset( $tmp );
                // make sql query to update 
                $this->sSql  = "UPDATE `$sTable` SET $sFields WHERE $sWhere $sOther;";
                // on PDO prepare statement
                $this->_oSTH = $this->prepare( $this->sSql );
                // bind pdo param for update statement
                $this->_bindPdoNameSpace( $aData );
                // bind pdo param for where clause
                $this->_bindPdoNameSpace( $aWhere );
                // try catch block start
                try {
                    // if PDO run
                    if ( $this->_oSTH->execute() ) {
                        // get affected rows
                        $this->iAffectedRows = $this->_oSTH->rowCount();
                        // close PDO
                        $this->_oSTH->closeCursor();
                        // return self object
                        return $this;
                    } else {
                        self::error( $this->_oSTH->errorInfo() );
                    }
                }
                catch ( PDOException $e ) {
                    // get pdo error and pass on error method
                    self::error( $e->getMessage() . ': ' . __LINE__ );
                } // try catch block end
            } else {
                self::error( 'update statement not in valid format..' );
            }
        } else {
            self::error( 'Table name not found..' );
        }
    }
    /**
     * Execute PDO Delete Query
     *
     * @param string $sTable
     * @param array $aWhere
     * @param string $sOther
     *
     * @return object PDO object
     */
    public function delete( $sTable, $aWhere = array(), $sOther = '' ) {
        // if table name not pass
        if ( !empty( $sTable ) ) {
            // check where condition array length
            if ( count( $aWhere ) > 0 && is_array( $aWhere ) ) {
                // make an temp array from where array data
                foreach ( $aWhere as $k => $v ) {
                    $tmp[] = "$k = :s_$k";
                }
                // join array values with AND Operator
                $sWhere = implode( ' AND ', $tmp );
                // delete temp array
                unset( $tmp );
                // set DELETE PDO Statement
                $this->sSql  = "DELETE FROM `$sTable` WHERE $sWhere $sOther;";
                // Call PDo Prepare Statement
                $this->_oSTH = $this->prepare( $this->sSql );
                // bind delete where param
                $this->_bindPdoNameSpace( $aWhere );
                // set array data
                $this->aData = $aWhere;
                // Use try Catch 
                try {
                    if ( $this->_oSTH->execute() ) {
                        // get affected rows
                        $this->iAffectedRows = $this->_oSTH->rowCount();
                        // close pdo
                        $this->_oSTH->closeCursor();
                        // return this object
                        return $this;
                    } else {
                        self::error( $this->_oSTH->errorInfo() );
                    }
                }
                catch ( PDOException $e ) {
                    // get pdo error and pass on error method
                    self::error( $e->getMessage() . ': ' . __LINE__ );
                } // end try catch here
            } else {
                self::error( 'Not a valid where condition..' );
            }
        } else {
            self::error( 'Table name not found..' );
        }
    }
    /**
     * Return PDO Query results array/json/xml type
     *
     * @param string $type
     *
     * @return mixed
     */
    public function results( $type = 'array' ) {
        switch ( $type ) {
            case 'array':
                // return array data
                return $this->aResults;
                break;
            case 'xml':
                //send the xml header to the browser
                header( "Content-Type:text/xml" );
                // return xml content
                return $this->helper()->arrayToXml( $this->aResults );
                break;
            case 'json':
                // set header as json
                header( 'Content-type: application/json; charset="utf-8"' );
                // return json encoded data
                return json_encode( $this->aResults );
                break;
        }
    }
    /**
     * Get Total Number Of Records in Requested Table
     *
     * @param string $sTable
     * @param string $where
     * @return number
     */
    public function count( $sTable = '', $aWhere = array(), $sOther = '' , $join = false) {
        // if table name not pass
        if ( !empty( $sTable ) ) {
			$sTable = $join == false ? '`'.$sTable.'`' : $sTable;
            if ( count( $aWhere ) > 0 && is_array( $aWhere ) ) {
                // set class where array
                $this->aData = $aWhere;
                // parse where array and get in temp var with key name and val
				$tmp1 = $this->customWhere($this->aData);
				// get where syntax with namespace
				$sWhere = $tmp1['where'];
                // unset temp var
                unset( $tmp );
                // set class sql property
                $this->sSql = "SELECT COUNT(*) AS NUMROWS FROM $sTable WHERE $sWhere $sOther;";
            }else{
                // create count query
                $this->sSql = "SELECT COUNT(*) AS NUMROWS FROM $sTable $sOther;";
            }
            // pdo prepare statement
            $this->_oSTH = $this->prepare( $this->sSql );
			// if where condition has valid array number
            if ( count( $aWhere ) > 0 && is_array( $aWhere ) ) {
               // bind pdo param
               $this->_bindPdoNameSpace( $aWhere );
            } // if end here
            try {
                if ( $this->_oSTH->execute() ) {
                    // fetch array result
                    $this->aResults = $this->_oSTH->fetch();
                    // close pdo
                    $this->_oSTH->closeCursor();
                    // return number of count
                    return $this->aResults['NUMROWS'];
                } else {
                    self::error( $this->_oSTH->errorInfo() );
                }
            }
            catch ( PDOException $e ) {
                // get pdo error and pass on error method
                self::error( $e->getMessage() . ': ' . __LINE__ );
            }
        } else {
            self::error( 'Table name not found..' );
        }
    }
    /**
     * Truncate a MySQL table
     *
     * @param string $sTable
     * @return bool
     */
    public function truncate($sTable =''){
        // if table name not pass
        if ( !empty( $sTable ) ) {
            // create count query
            $this->sSql  = "TRUNCATE TABLE `$sTable`;";
            // pdo prepare statement
            $this->_oSTH = $this->prepare( $this->sSql );
            try {
                if ( $this->_oSTH->execute() ) {
                    // close pdo
                    $this->_oSTH->closeCursor();
                    // return number of count
                    return true;
                } else {
                    self::error( $this->_oSTH->errorInfo() );
                }
            }
            catch ( PDOException $e ) {
                // get pdo error and pass on error method
                self::error( $e->getMessage() . ': ' . __LINE__ );
            }
        } else {
            self::error( 'Table name not found..' );
        }
    }

    /**
     * Drop a MySQL table
     *
     * @param string $sTable
     * @return bool
     */
    public function drop($sTable =''){
        // if table name not pass
        if ( !empty( $sTable ) ) {
            // create count query
            $this->sSql  = "DROP TABLE `$sTable`;";
            // pdo prepare statement
            $this->_oSTH = $this->prepare( $this->sSql );
            try {
                if ( $this->_oSTH->execute() ) {
                    // close pdo
                    $this->_oSTH->closeCursor();
                    // return number of count
                    return true;
                } else {
                    self::error( $this->_oSTH->errorInfo() );
                }
            }
            catch ( PDOException $e ) {
                // get pdo error and pass on error method
                self::error( $e->getMessage() . ': ' . __LINE__ );
            }
        } else {
            self::error( 'Table name not found..' );
        }
    }
    /**
     * Show Error to user
     */
    public function errorCustom() {
        die( "<div>There seems to be some issue with the DB connection.</div>" );
    }

}

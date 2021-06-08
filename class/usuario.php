<?php 
    include_once '../db/db.php';

    class Usuario extends DB
    {
        private $id;
        private $nombre;
        private $apellido;
        private $fechaNacimiento;
        private $genero;

        public function __construt($id, $nombre, $apellido, $fechaNacimiento, $genero){
            $this->id = $id;
            $this->nombre = $nombre;
            $this->apellido = $apellido;
            $this->fechaNacimiento = $fechaNacimiento;
            $this->genero = $genero;
        }

        public function setId( $id ){
            $this->id = $id;
            return $this;
        }

        public function getId(){
            return $this->id;
        }

        public function setNombre( $nombre ){
            $this->nombre = $nombre;
            return $this;
        }

        public function getNombre(){
            return $this->nombre;
        }

        public function setApellido( $apellido ){
            $this->apellido = $apellido;
            return $this;
        }

        public function getApellido(){
            return $this->apellido;
        }

        public function setFechaNacimiento( $fechaNacimiento ){
            $this->fechaNacimiento = $fechaNacimiento;
            return $this;
        }

        public function getFechaNacimiento(){
            return $this->fechaNacimiento;
        }

        public function setGenero( $genero ){
            $this->genero = $genero;
            return $this;
        }

        public function getGenero(){
            return $this->genero;
        }

        public function __toString()
        {
            return $this->nombre.' '.
                $this->apellido.' ('. 
                $this->fechaNacimiento.' ,'.
                $this->genero.')';
        }

        /**
         * Función encargada de realizar el guardado de un usuario nuevo
         * en base de datos
         */
        public function guardarUsuario( $usuario ){

            $query = 'INSERT INTO usuario ( id, nombre, apellido, fecha_nacimiento, saldo ) VALUES ( :id, :nombre, :apellido, :fechaNacimiento, :saldo )';
            $query = $this->connect()->prepare( $query );
            $query->execute( [ 'id' => $usuario['id'], 'nombre' => $usuario['nombre'], 'apellido' => $usuario['apellido'], 'fechaNacimiento' => $usuario['fechaNacimiento'], 'saldo' => $usuario['saldo'] ] );
            
            return $query;
        }

        /**
         * Función encargada de realizar la actualización de saldo para el usuario seleccionado
         */
        public function actualizarSaldoUsuario( $usuario ){
            $query = 'UPDATE usuario set saldo = :saldo, modified = :modified WHERE id = :id';
            $query = $this->connect()->prepare( $query );
            $query->execute( [ 'id' => $usuario['id'], 'saldo' => $usuario['saldo'], 'modified' => date('Y-m-d H:i:s') ] );
            
            return $query;
        }

        /**
         * Función encargada de realizar el movimiento de valores entre usuarios
         */
        public function realizarTrasladoSaldo( $item ){

            $queryOrigen    = 'UPDATE usuario set saldo = :saldoOrigen, modified = :modified WHERE id = :idOrigen';
            $queryDestino   = 'UPDATE usuario set saldo = :saldoDestino, modified = :modified WHERE id = :idDestino';

            $queryOrigen = $this->connect()->prepare( $queryOrigen );
            $queryDestino = $this->connect()->prepare( $queryDestino );

            $queryOrigen->execute( [ 'idOrigen' => $item['idOrigen'], 'saldoOrigen' => $item['saldo'], 'modified' => date('Y-m-d H:i:s') ] );
            $queryDestino->execute( [ 'idDestino' => $item['idDestino'], 'saldoDestino' => $item['saldoDestino'], 'modified' => date('Y-m-d H:i:s') ] );

            return $queryDestino;
        }

        /**
         * Función encargada de realizar la consulta de los datos de usuarios a partir 
         * de su id
         */
        public function consultarUsuario( $id ){

            $query = "SELECT * from usuario where id = :id and estado = 1";
            $query = $this->connect()->prepare( $query );
            $query->execute(['id' => $id]);
            return $query;
        }

        /**
         * Función encargada de realizar la consulta del listado completo del usuarios
         * desde la base de datos
         */
        public function consultarUsuarios(){

            $query = "SELECT * from usuario";
            $query = $this->connect()->query( $query );

            return $query;
        }

        /**
         * Función encargada de realizar la inactivación de usuarios
         */
        public function inactivarUsuario( $id ){
            $query = 'UPDATE usuario set estado = 0, modified = :modified WHERE id = :id';
            $query = $this->connect()->prepare( $query );
            $query->execute( [ 'id' => $id, 'modified' => date('Y-m-d H:i:s') ] );

            return $query;
        }

    }


?>
<?php

namespace app\controllers;

use app\models\Usuario\SolicitudRemiseriaModel;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\TipoUsuario;
use app\models\Usuario\PSFormularioSolicitudRegistrarAgenciaModel;
use app\models\Usuario\PSFormularioSolicitarServcioRemiseriaModel;
use app\models\Usuario\ListaHistorialViajesUsuarioModel;
use app\models\Usuario\ListaHistorialCalificacionesUsuarioModel;
use app\models\Usuario\CalificacionServicioModel;
use app\models\Agencia\ViajesGridModel;
use app\models\Agencia\GridModel;

class ClienteController extends Controller {

    public $layout = 'mainCliente';                                             //se asocia al layout predeterminado

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'], //solo debe aplicarse a las acciones login, logout , admin,recepcionista, chofer y cliente. Todas las demas acciones no estan sujetas al control de acceso
                'rules' => [                              //reglas
                    //el administrador tiene permisos sobre las siguientes acciones
                    ['actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'], //El arroba es para el usuario autenticado
                        'matchCallback' => function ($rule, $action) {                    //permite escribir la l?gica de comprobaci?n de acceso arbitraria, las paginas que se intentan acceder solo pueden ser permitidas si es un...
                    return TipoUsuario::usuarioCliente(Yii::$app->user->identity->RolID);
                    //Llamada al m?todo que comprueba si es un administrador
                    //Retorno el metodo del modelo que comprueba el tipo de usuario que es por el rol (1,2,3,4) etc y que devuelve true o false
                }
                    ]
                ]
            ]
        ];
    }

    public function actions() {
        if (!Yii::$app->user->isGuest) {                                                                              //si el usuario esta logeado, o sea no es invitado
            if (Yii::$app->user->identity->RolID == 1) {                                                                //si el usuario es administrador
                Yii::$app->errorHandler->errorAction = 'agencia/error';                                               //se muestra la pantalla de error de agencia y su respectivo layout
            } elseif (Yii::$app->user->identity->RolID == 2) {
                Yii::$app->errorHandler->errorAction = 'recepcionista/error';
            } elseif (Yii::$app->user->identity->RolID == 3) {
                Yii::$app->errorHandler->errorAction = 'chofer/error';
            } elseif (Yii::$app->user->identity->RolID == 4) {
                Yii::$app->errorHandler->errorAction = 'cliente/error';
            } else {
                Yii::$app->errorHandler->errorAction = 'site/error';
            }
        } else {                                                                                                      //sino (si el usuario es invitado) se muestra la pagina de error del site
            Yii::$app->errorHandler->errorAction = 'site/error';
        }
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex() {
        $model = new SolicitudRemiseriaModel();
        if ($model->load(Yii::$app->request->post()) && ($model->GuardarViaje() === true)) {
            return $this->redirect(['cliente/listar_historial_viajes']);
        }
        return $this->render("index", ['model' => $model]);
    }
    /*
    public function actionCalificar_servicio() {
        $model = new CalificacionServicioModel();
        if ($model->load(Yii::$app->request->post()) && ($model->setCalificacion() === true))
        {
            Yii::$app->session->setFlash('calificacionSeteada');
            return $this->redirect(['listarHistorialCalificaciones']);
        }
        else{
            if (isset(Yii::$app->session['actualizar'])) {
                $viajeSelected = Yii::$app->session['actualizar'];
                $model->setUpdateInfo($viajeSelected);
            }
            else {
                $viajeSelected = null;
            }
            //$selection=(array)Yii::$app->request->post('keylist');

            return $this->renderAjax("calificarServicio", ['model' => $model]);
        }
    }*/

    public function actionLista_historial_calificaciones() {
        $model = new ListaHistorialCalificacionesUsuarioModel();
        $model->setDataProvider();
        return $this->render("listaHistorialCalificaciones", ['model' => $model]);
    }

    public function actionCalificar_servicio() {
        $model = new CalificacionServicioModel();
        if (\Yii::$app->request->isPost)
        {
            $viajeSelected = Yii::$app->session['actualizar'];
            $model->setUpdateInfo($viajeSelected);
            //Yii::$app->session->setFlash('Calificacion Exitosa!');
            //return $this->redirect(['cliente/listar_historial_calificaciones']);
        }
        if ($model->load(Yii::$app->request->post()) && ($model->setCalificacion() === true)) {
            Yii::$app->session->setFlash('Calificacion Exitosa!');
            return $this->redirect(['cliente/lista_historial_calificaciones']);
            }
        return $this->renderAjax("calificarServicio", ['model' => $model]);
    }

    public function actionLista_historial_viajes() {
        $model = new ListaHistorialViajesUsuarioModel();
        $model->setDataProvider();
        if (\Yii::$app->request->isPost)  {
            if (\Yii::$app->request->isAjax) {
                $selection=(array)Yii::$app->request->post('keylist');
                $personaselected=$model->dataProvider->allModels[$selection[0]];
                Yii::$app->session['actualizar'] = $personaselected;

            }
            //else{}*/
        }
        return $this->render("listaHistorialViajes", ['model' => $model]);
    }

    /*
    public function actionCerrarViaje() {                      //renderiza el index de la carpeta agencia dentro de views
      $model = new ListaHistorialViajesUsuarioModel();
      $model->cerrarViaje();
      return $this->renderAjax("listaHistorialViajes", ['model' => $model]);
      }*/
}

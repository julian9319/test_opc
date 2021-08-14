<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Form\ProductType;
use App\Entity\Product;


class ProductController extends Controller
{

    public function index(): Response
    {
        return $this->render('product/index.html.twig');
    } 

    public function productJson(): JsonResponse
    {

        $em = $this->getDoctrine()->getManager();

        $result=[];

   
      $sql = " SELECT p FROM App\Entity\Product p ";
      $query = $em->createQuery($sql);

        $entities = $query->getResult();

        foreach ($entities as $key => $value) {

            $result[]=[
                'id'=> $value->getId(),
                'code'=> $value->getCode(),
                'name'=> $value->getName(),
                'brand'=> $value->getBrand(),
                'active'=> (($value->getActive())?'Activo':'Inactivo'),
                'price'=> '$ '.number_format($value->getPrice(), 0, '', '.') ,
                'fk_category'=> $value->getFkCategory()->getName(),
            ];
        }
    
        $em->getConnection()->close();

        $response = new JsonResponse($result);

        return $response;
    }

    public function new(): Response 
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function create(Request $request): JsonResponse
    {

        if ($request->get('token') === $this->get('security.csrf.token_manager')->getToken('intention')->getValue()) {

            $result=[];

            $connection = $this->getDoctrine()->getManager()->getConnection();

            $product = new Product();
            $form = $this->createForm(ProductType::class, $product);

            $form->handleRequest($request);

            $sql='select count(id) as contador from product where code=? and name=?';
            $statement = $connection->prepare($sql);
            $statement->bindValue(1, $product->getCode());
            $statement->bindValue(2, $product->getName());

            $statement->execute();

            $contador = $statement->fetch();
            if ($contador['contador'] > 0) {

              $result['status']=0;
              $result['message']='El código y el nombre ya existen en el sistema.';

            } else {

              $sql='INSERT INTO product (code, name, description, brand, active, price, fk_category) VALUES 
                  (?,?,?,?,?,?,?);';

              $transacction = $connection->prepare($sql);

              $transacction->bindValue(1, $product->getCode());
              $transacction->bindValue(2, $product->getName());
              $transacction->bindValue(3, $product->getDescription());
              $transacction->bindValue(4, $product->getBrand());
              $transacction->bindValue(5, $product->getActive());
              $transacction->bindValue(6, $product->getPrice());

              $category=$product->getFkCategory()->getId();
              $transacction->bindParam(7, $category);

              $transacction->execute();
            }

            $response = new JsonResponse($result);

            return $response;
        }

        throw new \Exception("Error Processing Request", 1);
        
    }

    public function edit(Request $request): Response 
    {
        $em = $this->getDoctrine()->getManager();
        $id=$request->get('id');

        $product=$em->getRepository('App\Entity\Product')->findOneBy(['id'=>$id]);

        $form = $this->createForm(ProductType::class, $product);

        return $this->render('product/edit.html.twig', [
            'product'=>$product,
            'form' => $form->createView(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {

        if ($request->get('token') === $this->get('security.csrf.token_manager')->getToken('intention')->getValue()) {

            $result=[];

            $em=$this->getDoctrine()->getManager();
            $connection = $em->getConnection();

            $id=$request->get('id');
            $product = $em->getRepository('App\Entity\Product')->findOneBy(['id'=>$id]);
            $form = $this->createForm(ProductType::class, $product);

            $form->handleRequest($request);

            $product->setUpdatedAt(new \DateTime('now'));

            $sql='update product set code=?, name=?, description=?, brand=?, active=?, 
            price=?, fk_category=?, updated_at=? where id=?';

            $transacction = $connection->prepare($sql);

            $transacction->bindValue(1, $product->getCode());
            $transacction->bindValue(2, $product->getName());
            $transacction->bindValue(3, $product->getDescription());
            $transacction->bindValue(4, $product->getBrand());
            $transacction->bindValue(5, $product->getActive());
            $transacction->bindValue(6, $product->getPrice());

            $category=$product->getFkCategory()->getId();
            $transacction->bindParam(7, $category);

            $updatedAt=$product->getUpdatedAt()->format('Y-m-d H:i:s');
            $transacction->bindParam(8, $updatedAt);
            $transacction->bindParam(9, $id);

            $transacction->execute();

            $response = new JsonResponse($result);

            return $response;
        }

        throw new \Exception("Error Processing Request", 1);
        
    }


    public function excel(Request $request):Response
    {
        $fondoTitulos = array(
          'alignment' => array(
              'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT ,
          ),
          "fill" => array(
              'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
              "color" => array("rgb" => '092366'),
          ),
          "font" => array(
            "bold" => true,
            "color" => array("rgb" => "ffffff"),
          ),
          'alignment' => array(
              'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
          ),
          'borders' => array(
              'top' => array(
                  'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
              ),
              'bottom' => array(
                  'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
              ),
          ),
        );
        $bordeTabla = array(
          'borders' => array(
            'allBorders' => array(
              'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            )
          )
        );
        $textoCentrado = array(
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            )
        );
        $titulosPreguntasUnoDos = array( 
          "fill" => array(
              'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
              "color" => array("rgb" => 'ff9900'),
              ),
          "font" => array(
              "bold" => true,
              "color" => array("rgb" => "ffffff")
            ,)
        );
        $textoCentrado = array(
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            )
        );

        $objPHPExcel = new Spreadsheet();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('informe');
        $objPHPExcel->getProperties()->setTitle("informe");

        $row=1;

        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'CÓDIGO');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, 'NOMBRE');
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, 'DESCRIPCIÓN');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, 'MARCA');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, 'ESTADO');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, 'PRECIO');
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, 'CATEGORÍA');
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, 'FECHA CREACIÓN');
      
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);

        $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':H'.$row)->applyFromArray($textoCentrado);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':H'.$row)->applyFromArray($fondoTitulos);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':H'.$row)->applyFromArray($bordeTabla);

        $em = $this->getDoctrine()->getManager();

        $sql = " SELECT p FROM App\Entity\Product p ";
        $query = $em->createQuery($sql);

        $entities = $query->getResult();

        $row++; 

        foreach ($entities as $key => $value) {
          
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $value->getCode());
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $value->getName());
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $value->getDescription());
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $value->getBrand());

            $isActive=($value->getActive() == 1)?'Activo':'Inactivo';

            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $isActive);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $value->getPrice());
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $value->getFkCategory()->getName());
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $value->getCreatedAt()->format('Y-m-d H:i:s'));

            $row++;
        }

        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: text/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment;filename="products_list.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = IOFactory::createWriter($objPHPExcel,'Xlsx');
        $objWriter->save('php://output'); 

        return new Response;
    }

    public function delete(Request $request): JsonResponse
    {
        $result=[
            'status'=>0
        ];

        try {

            $em=$this->getDoctrine()->getManager();
            $connection=$em->getConnection();

            $id=$request->get('id');

            $transacction=$connection->prepare("DELETE FROM product WHERE id = ?");
            $transacction->bindValue(1, $id);
            $transacction->execute();

            $result['status']=1;

        } catch (\Exception $e) {

          $response->setStatusCode(500);

        }

        $response = new JsonResponse($result);

        return $response;
    }

}

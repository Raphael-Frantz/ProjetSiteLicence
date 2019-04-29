<?php
class MonDocument extends FPDF {
    
    public $data;
    
    function Header() {
        if($this->PageNo() == 1) {
            $this->SetFont("times", "B", 20);
            $this->Cell($this->GetPageWidth() - 20,7,utf8_decode($this->data['EC']->getCode()." - groupe ".Groupe::type2String($this->data['groupe']->getType())." ".$this->data['groupe']),0,0,"C");
            $this->Ln(15);

            $this->SetFont("times", "B", 12);
            $this->Cell(70,7,utf8_decode("Intervenant : "),1, 0);
            $this->Cell(45,7,utf8_decode("Date : "),1,0);
            $this->Cell(45,7,utf8_decode("Heure : "),1,0);
            $this->Cell(30,7,utf8_decode(Groupe::type2String($this->data['groupe']->getType())." n° : "),1,0);
            $this->Ln(10);
        }

        $this->SetFont("times", "B", 12);
        $this->Cell(10,7,utf8_decode(""),0, 0, "C");
        $this->Cell(140,7,utf8_decode("NOM - PRENOM"),1, 0, "C");
        $this->Cell(40,7,utf8_decode("SIGNATURE"),1, 0, "C");
        $this->Ln(7);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont("times", "I", 8);
        $this->Cell(0, 10, "Page ".$this->PageNo()."/{nb}", 0, 0, "C");
    }
}
$pdf = new MonDocument();
$pdf->AliasNbPages();
$pdf->data = $data;
$pdf->AddPage();

$i = 1;
foreach($data['etudiants'] as $etudiant) {
    $pdf->SetFont("times", "B", 12);
    $pdf->Cell(10,7,utf8_decode($i),1, 0, "C");
    $pdf->SetFont("times", "", 12);
    $pdf->Cell(140,7,utf8_decode($etudiant['nom']." ".$etudiant['prenom']),1, 0, "L");
    $pdf->Cell(40,7,utf8_decode(""),1, 0, "C");    
    $pdf->Ln(7);
    $i++;
}

header("Content-Type: application/pdf");
$pdf->Output('D', $data['nomFichier']);
exit();
<?php

namespace App\Reg;

/**
 * معرّفات مجموعات جدول القيم المرجعية (reg_ltcolumns.grouped).
 * القيم نفسها قابلة للتعديل من الواجهة، أما أرقام المجموعات فثابتة في النظام.
 */
enum LtGroup: int
{
    case Gender = 1001;
    case Nationality = 1002;
    case State = 1003;
    case Admission = 2001;
    case AcademicYear = 2003;
    case Status = 2004;
    case Installment = 2005;
    case Stady = 2006;
    case XStatus = 2007;
    case DiscountPercent = 2008;
    case InvoiceType = 4001;
    case StudentAccount = 4002;
}

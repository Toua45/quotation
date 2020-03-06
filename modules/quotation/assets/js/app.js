import * as test from './quotation';
import {customers} from '../../../../admin130mdhxh9/test';
import {QuotationModule} from './test';

console.log(customers);
QuotationModule.customerList();
QuotationModule.customers(QuotationModule.customerList());


import * as test from './quotation';
import {customers} from '../../../../adminLionel/test';
import {QuotationModule} from './test';

console.log(customers);
QuotationModule.customerList();
QuotationModule.customers(QuotationModule.customerList());


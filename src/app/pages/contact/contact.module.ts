import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

// 3rd-Party plugins variables
import { NgSelectModule } from '@ng-select/ng-select';
import { InlineSVGModule } from 'ng-inline-svg-2';
import { QuillModule } from 'ngx-quill';

import { 
  NgbDropdownModule,
} from '@ng-bootstrap/ng-bootstrap';

import { ContactRoutingModule } from './contact-routing.module';
import { ContactComponent } from './contact.component';

import { CoreModule } from '../../core';

@NgModule({
  declarations: [
    ContactComponent
  ],
  imports: [
    CommonModule,
    ContactRoutingModule,
    FormsModule, ReactiveFormsModule,
    NgbDropdownModule,
    NgSelectModule,
    QuillModule.forRoot(),
    InlineSVGModule,
    CoreModule
  ]
})
export class ContactModule { }

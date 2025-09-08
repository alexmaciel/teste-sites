import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

// 3rd-Party plugins variables
import { InlineSVGModule } from 'ng-inline-svg-2';
import { NgbModalModule } from '@ng-bootstrap/ng-bootstrap';
import { LocalizeRouterModule } from '@gilsdav/ngx-translate-router';
import { TranslateModule } from '@ngx-translate/core';

// Pagination
import { NgPagination } from './helpers/paginator/ng-pagination/ng-pagination.component';
import { PaginatorComponent } from './helpers/paginator/paginator.component';

// Pipes
import { 
  TruncatetextPipe,
  DateLocalePipe,
  SafePipe
} from './pipes';

// Components
import { 
  CasesComponent,
  BlogComponent,
  VideoComponent,
  // Map
  LocationComponent
} from './components';

// Plugins
import { 
  NgxSwiperModule,
  NgxYouTubePlayerModule,
  NgxOptimizedImageModule
 } from '../shared';

@NgModule({
  declarations: [
    // Pipes
    TruncatetextPipe,
    DateLocalePipe,
    SafePipe,
    // Components
    CasesComponent,
    VideoComponent, 
    BlogComponent,
    // Map
    LocationComponent,
    // Pagination
    NgPagination,
    PaginatorComponent,       
  ],
  imports: [
    CommonModule,
    FormsModule, 
    // Translate
    TranslateModule,
    LocalizeRouterModule,    
    // Plugins
    NgxSwiperModule,
    NgxYouTubePlayerModule,
    NgxOptimizedImageModule,
    InlineSVGModule,  
    NgbModalModule,
  ],
  exports: [
    // Translate
    TranslateModule,
    LocalizeRouterModule,  
    // Pipes
    TruncatetextPipe,
    DateLocalePipe,
    SafePipe,
    // Components
    CasesComponent,
    VideoComponent, 
    BlogComponent,
    // Map
    LocationComponent,
    // Pagination
    NgPagination,
    PaginatorComponent,          
  ],
  schemas: [CUSTOM_ELEMENTS_SCHEMA]
})
export class CoreModule { }

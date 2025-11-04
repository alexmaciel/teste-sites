import { Component, OnDestroy, OnInit } from '@angular/core';
import { Subscription } from 'rxjs';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { 
  TechnologyService,
  TechnologyItemService
} from '../../../core';

import { VideosComponent } from './videos/videos.component';

@Component({
  selector: 'app-technology',
  templateUrl: './technology.component.html'
})
export class TechnologyComponent implements OnInit, OnDestroy {

  private subscriptions: Subscription[] = [];

  constructor(
    private modal: NgbModal,
    // Services
    public technology: TechnologyService,
    public items: TechnologyItemService
  ) {}   

  ngOnInit(): void {
    this.loadTechnology();
    this.loadTechnologydItems();
  }  
    
  loadTechnology() {
    const sb = this.technology.getTechnology().pipe(
    ).subscribe();
    this.subscriptions.push(sb);   
  }  
  
  loadTechnologydItems() {
    const sb = this.items.getItems().pipe(
    ).subscribe();
    this.subscriptions.push(sb);       
  }   
  
  openVideo() {
    const modalRef = this.modal.open(VideosComponent, { size: 'xl', centered: true, windowClass: 'modal-custom' });
  }  

  ngOnDestroy(): void {
    this.subscriptions.forEach((sb) => sb.unsubscribe());
  }  
}

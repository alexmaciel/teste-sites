import { Component, OnDestroy, OnInit } from '@angular/core';
import { Subscription } from 'rxjs';

import { PartneService } from '../../core/services';

@Component({
  selector: 'app-partners',
  templateUrl: './partners.component.html'
})
export class PartnersComponent implements OnInit, OnDestroy {
  private subscriptions: Subscription[] = [];
  
  constructor(
    // Services
    public partners: PartneService,
  ){}

  ngOnInit(): void {
    this.loadPartners();
  }

  loadPartners() {
    const sb = this.partners.getPartners().pipe(
    ).subscribe();
    this.subscriptions.push(sb);       
  }  

  ngOnDestroy() {
    this.subscriptions.forEach((sb) => sb.unsubscribe());
  }
}

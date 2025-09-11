import { Component, Input } from '@angular/core';

import { TranslationService } from '../../../core';

@Component({
  selector: 'app-content',
  templateUrl: './content.component.html',
  styles: [`
    .loading-screen {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .spinner {
      width: 40px;
      height: 40px;
      border: 4px solid #f3f3f3;
      border-top: 4px solid #3498db;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  `]    
})
export class ContentComponent {
  @Input() appContentContainer?: 'fixed' | 'fluid';
  @Input() appContentContainerClass = '';
  @Input() contentContainerCSSClass = '';

  constructor(
    // Services
    public translation: TranslationService,
  ) {}
}

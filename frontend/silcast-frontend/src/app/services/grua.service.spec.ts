import { TestBed } from '@angular/core/testing';

import { GruaService } from './grua.service';

describe('GruaService', () => {
  let service: GruaService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(GruaService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});

import {
  EateryBranchesCollection,
  EateryNationwideBranch,
} from '@/types/EateryTypes';

/** Where a branch sits in the grouped tree. Area is the literal '_' when it has none. */
export type BranchPath = {
  country: string;
  county: string;
  town: string;
  area: string;
};

export type LocatedBranch = BranchPath & { branch: EateryNationwideBranch };

type BranchNode = EateryNationwideBranch[] | { [key: string]: BranchNode };

/** Walks every level of the tree, keeping the path each branch was found at. */
export const flattenBranches = (
  collection: EateryBranchesCollection,
): LocatedBranch[] => {
  const flattened: LocatedBranch[] = [];

  Object.entries(collection).forEach(([country, counties]) => {
    Object.entries(counties).forEach(([county, towns]) => {
      Object.entries(towns).forEach(([town, areas]) => {
        Object.entries(areas).forEach(([area, branches]) => {
          branches.forEach((branch) =>
            flattened.push({ country, county, town, area, branch }),
          );
        });
      });
    });
  });

  return flattened;
};

export const countBranches = (node: BranchNode): number => {
  if (Array.isArray(node)) {
    return node.length;
  }

  return Object.values(node).reduce<number>(
    (total, child) => total + countBranches(child),
    0,
  );
};

const matches = ({ branch }: LocatedBranch, term: string): boolean =>
  [
    branch.name,
    branch.town.name,
    branch.county.name,
    branch.area?.name,
    branch.location.address,
  ].some((value) => value?.toLowerCase().includes(term));

/**
 * Rebuilds the tree from only the branches matching the term, rather than
 * pruning in place — the flattened walk already preserves the original ordering.
 */
export const filterBranches = (
  collection: EateryBranchesCollection,
  term: string,
): EateryBranchesCollection => {
  const needle = term.trim().toLowerCase();

  if (!needle) {
    return collection;
  }

  const filtered: EateryBranchesCollection = {};

  flattenBranches(collection)
    .filter((entry) => matches(entry, needle))
    .forEach(({ country, county, town, area, branch }) => {
      filtered[country] ??= {};
      filtered[country][county] ??= {};
      filtered[country][county][town] ??= {};
      filtered[country][county][town][area] ??= [];

      filtered[country][county][town][area].push(branch);
    });

  return filtered;
};
